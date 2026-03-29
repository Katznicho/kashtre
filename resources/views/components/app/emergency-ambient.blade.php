@props([
    'enabled' => false,
    'activeAlert' => null,
    'displayDuration' => 0,
    'statusUrl' => null,
    'poll' => true,
    'pollInterval' => 5000,
])

@if($enabled)
    @php
        $initialAlert = $activeAlert ? [
            'id' => $activeAlert->id,
            'color' => $activeAlert->color ?? 'red',
            'triggered_at' => $activeAlert->triggered_at?->timestamp,
            'display_duration' => (int) $displayDuration,
        ] : null;
    @endphp

    <div
        id="emergency-ambient"
        style="position:fixed; inset:0; z-index:20; pointer-events:none; opacity:0; transition:opacity 220ms ease, background 220ms ease, box-shadow 220ms ease;"
        aria-hidden="true"
    ></div>

    <script>
    (function () {
        if (!window.KashtreEmergencyAmbient) {
            window.KashtreEmergencyAmbient = (function () {
                var el = null;
                var dismissTimer = null;
                var pollTimer = null;
                var activeEmergencyId = null;

                var EM_COLOR_RGB_MAP = {
                    red: '220, 38, 38',
                    green: '22, 163, 74',
                    yellow: '202, 138, 4',
                    amber: '217, 119, 6',
                    blue: '37, 99, 235'
                };

                function clearDismissTimer() {
                    if (dismissTimer) {
                        clearTimeout(dismissTimer);
                        dismissTimer = null;
                    }
                }

                function renderAmbient(color) {
                    if (!el) return;
                    var rgb = EM_COLOR_RGB_MAP[color] || EM_COLOR_RGB_MAP.red;
                    el.style.background =
                        'linear-gradient(180deg, rgba(' + rgb + ',0.28) 0%, rgba(' + rgb + ',0.18) 24%, rgba(' + rgb + ',0.10) 58%, rgba(' + rgb + ',0.06) 100%)';
                    el.style.boxShadow =
                        'inset 0 0 0 1px rgba(' + rgb + ',0.14), inset 0 0 220px rgba(' + rgb + ',0.12)';
                    el.style.opacity = '1';
                }

                function hide() {
                    if (!el) return;
                    clearDismissTimer();
                    el.style.opacity = '0';
                }

                function show(color, triggeredAt, displayDuration, id) {
                    if (!el) return;
                    activeEmergencyId = id ?? activeEmergencyId;
                    renderAmbient(color);
                    clearDismissTimer();

                    if (displayDuration > 0 && triggeredAt) {
                        var elapsed = Math.floor(Date.now() / 1000) - Number(triggeredAt);
                        var remaining = (Number(displayDuration) - elapsed) * 1000;
                        if (remaining <= 0) {
                            activeEmergencyId = null;
                            hide();
                            return;
                        }
                        dismissTimer = setTimeout(function () {
                            activeEmergencyId = null;
                            hide();
                        }, remaining);
                    }
                }

                function sync(data) {
                    if (data && data.active) {
                        if (data.id !== activeEmergencyId || !el || el.style.opacity !== '1') {
                            show(data.color, data.triggered_at, data.display_duration, data.id);
                        }
                        return;
                    }

                    activeEmergencyId = null;
                    hide();
                }

                function pollOnce(statusUrl) {
                    if (!statusUrl) return;
                    fetch(statusUrl, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    }).then(function (response) {
                        return response.json();
                    }).then(function (data) {
                        sync(data);
                    }).catch(function () {});
                }

                function mount(config) {
                    el = document.getElementById(config.elementId || 'emergency-ambient');
                    if (!el) return;

                    clearDismissTimer();
                    if (pollTimer) {
                        clearInterval(pollTimer);
                        pollTimer = null;
                    }

                    activeEmergencyId = null;
                    hide();

                    if (config.initialAlert) {
                        show(
                            config.initialAlert.color,
                            config.initialAlert.triggered_at,
                            config.initialAlert.display_duration,
                            config.initialAlert.id
                        );
                    }

                    if (config.autoPoll && config.statusUrl) {
                        pollTimer = setInterval(function () {
                            pollOnce(config.statusUrl);
                        }, config.pollInterval || 5000);
                    }
                }

                return {
                    mount: mount,
                    show: show,
                    hide: hide,
                    sync: sync,
                    pollOnce: pollOnce,
                };
            })();
        }

        window.KashtreEmergencyAmbient.mount({
            elementId: 'emergency-ambient',
            statusUrl: @json($statusUrl ?? route('emergency.status')),
            autoPoll: {{ $poll ? 'true' : 'false' }},
            pollInterval: {{ (int) $pollInterval }},
            initialAlert: @json($initialAlert),
        });
    })();
    </script>
@endif
