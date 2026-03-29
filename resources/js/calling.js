export default function callingSystem() {
    return {
        // State
        callState: 'idle', // idle, incoming, calling, connected, ended
        remoteUser: null,
        callId: null,
        isMuted: false,
        duration: 0,
        durationFormatted: '00:00',
        onlineUsers: [],

        // Internal variables
        peerConnection: null,
        localStream: null,
        remoteStream: null,
        timerInterval: null,
        ringtoneAudio: null,
        outgoingRingtoneAudio: null,
        reconnectAttempts: 0,
        maxReconnectAttempts: 3,

        // ICE servers config (Google STUN)
        iceServers: {
            iceServers: [
                { urls: 'stun:stun.l.google.com:19302' },
                { urls: 'stun:stun1.l.google.com:19302' }
            ]
        },

        init() {
            // Setup Web Audio API ringtones (no file dependency)
            this.ringtoneAudio = this.createBeepPattern(440, 800, 400);
            this.outgoingRingtoneAudio = this.createBeepPattern(480, 500, 500);

            const userId = document.querySelector('meta[name="user-id"]')?.content;
            const businessId = document.querySelector('meta[name="business-id"]')?.content;

            if (userId && window.Echo) {
                // Private channel for direct call events
                window.Echo.private(`user.${userId}`)
                    .listen('IncomingCall', (e) => {
                        console.log('Incoming call:', e);
                        if (this.callState !== 'idle') {
                            this.rejectCall(e.call_id, true);
                            return;
                        }
                        this.callId = e.call_id;
                        this.remoteUser = e.caller;
                        this.changeState('incoming');
                    })
                    .listen('CallAccepted', (e) => {
                        console.log('Call accepted by callee:', e);
                        if (this.callId === e.call_id) {
                            this.changeState('connected');
                            this.initiateWebRTC();
                        }
                    })
                    .listen('CallRejected', (e) => {
                        console.log('Call rejected:', e);
                        if (this.callId === e.call_id) {
                            this.changeState('ended');
                            setTimeout(() => this.resetCall(), 2000);
                        }
                    })
                    .listen('CallEnded', (e) => {
                        console.log('Call ended:', e);
                        if (this.callId === e.call_id) {
                            this.changeState('ended');
                            setTimeout(() => this.resetCall(), 2000);
                        }
                    })
                    .listen('WebRTCSignal', async (e) => {
                        if (this.callId !== e.call_id) return;

                        if (!this.peerConnection) {
                            await this.setupPeerConnection();
                        }

                        if (e.type === 'offer') {
                            await this.peerConnection.setRemoteDescription(new RTCSessionDescription(e.data));
                            const answer = await this.peerConnection.createAnswer();
                            await this.peerConnection.setLocalDescription(answer);
                            this.sendSignal('answer', answer);
                        } else if (e.type === 'answer') {
                            await this.peerConnection.setRemoteDescription(new RTCSessionDescription(e.data));
                        } else if (e.type === 'candidate') {
                            await this.peerConnection.addIceCandidate(new RTCIceCandidate(e.data));
                        }
                    });

                // Presence channel — real-time online users (no polling)
                if (businessId) {
                    window.Echo.join(`presence-business.${businessId}`)
                        .here((users) => {
                            this.onlineUsers = users.filter(u => String(u.id) !== String(userId));
                        })
                        .joining((user) => {
                            if (!this.onlineUsers.find(u => u.id === user.id)) {
                                this.onlineUsers.push(user);
                            }
                        })
                        .leaving((user) => {
                            this.onlineUsers = this.onlineUsers.filter(u => u.id !== user.id);
                        })
                        .error((error) => {
                            console.error('Presence channel error:', error);
                        });
                }
            }
        },

        // --- Ringtone helpers (Web Audio API) ---

        createBeepPattern(frequency, onMs, offMs) {
            let ctx = null, interval = null, playing = false;
            return {
                play() {
                    if (playing) return;
                    playing = true;
                    ctx = new (window.AudioContext || window.webkitAudioContext)();
                    const beep = () => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = frequency;
                        osc.type = 'sine';
                        gain.gain.setValueAtTime(0.3, ctx.currentTime);
                        osc.start();
                        osc.stop(ctx.currentTime + onMs / 1000);
                    };
                    beep();
                    interval = setInterval(beep, onMs + offMs);
                },
                stop() {
                    if (!playing) return;
                    playing = false;
                    clearInterval(interval);
                    interval = null;
                    ctx?.close();
                    ctx = null;
                }
            };
        },

        // --- Call Control Actions ---

        async initiateCall(userId) {
            if (this.callState !== 'idle') return;

            try {
                await this.getLocalStream();

                const response = await axios.post('/calls/initiate', { callee_uuid: userId });
                this.callId = response.data.call_id;
                this.remoteUser = response.data.callee;
                this.changeState('calling');
            } catch (error) {
                console.error('Failed to initiate call', error);
                alert(error.response?.data?.error || 'Failed to initiate call');
                this.resetCall();
            }
        },

        async acceptCall() {
            try {
                await this.getLocalStream();
                await axios.post(`/calls/${this.callId}/accept`);
                this.changeState('connected');
                // Caller will initiate WebRTC offer upon receiving CallAccepted event
            } catch (error) {
                console.error('Failed to accept call', error);
                this.resetCall();
            }
        },

        async rejectCall(overrideCallId = null, isBusyFallback = false) {
            const idToReject = overrideCallId || this.callId;
            if (!idToReject) return;

            try {
                await axios.post(`/calls/${idToReject}/reject`);
                if (!isBusyFallback) {
                    this.resetCall();
                }
            } catch (error) {
                console.error('Failed to reject call', error);
            }
        },

        async cancelCall() {
            try {
                await axios.post(`/calls/${this.callId}/cancel`);
                this.resetCall();
            } catch (error) {
                console.error('Failed to cancel call', error);
            }
        },

        async endCall() {
            try {
                await axios.post(`/calls/${this.callId}/end`);
                this.changeState('ended');
                setTimeout(() => this.resetCall(), 2000);
            } catch (error) {
                console.error('Failed to end call', error);
                this.resetCall();
            }
        },

        // --- WebRTC Logic ---

        async getLocalStream() {
            if (this.localStream) return;
            this.localStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
        },

        async setupPeerConnection() {
            this.peerConnection = new RTCPeerConnection(this.iceServers);

            this.localStream.getTracks().forEach(track => {
                this.peerConnection.addTrack(track, this.localStream);
            });

            this.peerConnection.ontrack = (event) => {
                this.remoteStream = event.streams[0];
                const audioElement = document.getElementById('remoteAudio');
                if (audioElement && audioElement.srcObject !== this.remoteStream) {
                    audioElement.srcObject = this.remoteStream;
                    audioElement.play().catch(e => console.error("Audio playback failed", e));
                }
            };

            this.peerConnection.onicecandidate = (event) => {
                if (event.candidate) {
                    this.sendSignal('candidate', event.candidate);
                }
            };

            // Reconnection logic
            this.peerConnection.oniceconnectionstatechange = () => {
                const state = this.peerConnection?.iceConnectionState;
                console.log('ICE connection state:', state);

                if (state === 'disconnected') {
                    // Give it 3s to self-recover before attempting restart
                    setTimeout(() => {
                        if (this.peerConnection?.iceConnectionState === 'disconnected') {
                            this.attemptReconnect();
                        }
                    }, 3000);
                } else if (state === 'failed') {
                    this.attemptReconnect();
                } else if (state === 'connected' || state === 'completed') {
                    this.reconnectAttempts = 0;
                }
            };
        },

        async attemptReconnect() {
            if (!this.peerConnection || !this.callId) return;

            if (this.reconnectAttempts >= this.maxReconnectAttempts) {
                console.warn('Max reconnect attempts reached, ending call');
                this.endCall();
                return;
            }

            this.reconnectAttempts++;
            console.log(`ICE restart attempt ${this.reconnectAttempts}/${this.maxReconnectAttempts}`);

            try {
                const offer = await this.peerConnection.createOffer({ iceRestart: true });
                await this.peerConnection.setLocalDescription(offer);
                this.sendSignal('offer', offer);
            } catch (e) {
                console.error('ICE restart failed', e);
            }
        },

        async initiateWebRTC() {
            await this.setupPeerConnection();
            const offer = await this.peerConnection.createOffer();
            await this.peerConnection.setLocalDescription(offer);
            this.sendSignal('offer', offer);
        },

        sendSignal(type, data) {
            axios.post(`/calls/${this.callId}/signal`, {
                type: type,
                data: data
            }).catch(err => console.error('Signaling error:', err));
        },

        // --- UI & State Management ---

        changeState(newState) {
            this.callState = newState;

            this.ringtoneAudio.stop();
            this.outgoingRingtoneAudio.stop();

            if (newState === 'incoming') {
                this.ringtoneAudio.play();
            } else if (newState === 'calling') {
                this.outgoingRingtoneAudio.play();
            } else if (newState === 'connected') {
                this.startTimer();
            } else if (newState === 'ended') {
                this.stopTimer();
            }
        },

        toggleMute() {
            if (this.localStream) {
                this.isMuted = !this.isMuted;
                this.localStream.getAudioTracks().forEach(track => {
                    track.enabled = !this.isMuted;
                });
            }
        },

        startTimer() {
            this.duration = 0;
            this.formatDuration();
            this.timerInterval = setInterval(() => {
                this.duration++;
                this.formatDuration();
            }, 1000);
        },

        stopTimer() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
                this.timerInterval = null;
            }
        },

        formatDuration() {
            const minutes = Math.floor(this.duration / 60);
            const seconds = this.duration % 60;
            this.durationFormatted = `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
        },

        resetCall() {
            this.changeState('idle');
            this.remoteUser = null;
            this.callId = null;
            this.isMuted = false;
            this.duration = 0;
            this.durationFormatted = '00:00';
            this.reconnectAttempts = 0;

            if (this.peerConnection) {
                this.peerConnection.close();
                this.peerConnection = null;
            }
            if (this.localStream) {
                this.localStream.getTracks().forEach(track => track.stop());
                this.localStream = null;
            }
            this.remoteStream = null;

            const audioElement = document.getElementById('remoteAudio');
            if (audioElement) {
                audioElement.srcObject = null;
            }
        }
    };
}
