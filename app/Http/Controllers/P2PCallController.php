<?php

namespace App\Http\Controllers;

use App\Events\CallAccepted;
use App\Events\CallEnded;
use App\Events\CallRejected;
use App\Events\IncomingCall;
use App\Events\WebRTCSignal;
use App\Models\P2PCall;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class P2PCallController extends Controller
{
    /**
     * Initiate a call to another user
     */
    public function initiateCall(Request $request)
    {
        $request->validate([
            'callee_uuid' => 'required|string|exists:users,uuid',
        ]);

        $caller = Auth::user();
        $callee = User::where('uuid', $request->callee_uuid)->firstOrFail();

        // Can't call yourself
        if ($caller->id === $callee->id) {
            return response()->json(['error' => 'Cannot call yourself'], 422);
        }

        // Must be in the same business
        if ($caller->business_id !== $callee->business_id) {
            return response()->json(['error' => 'User not in your business'], 403);
        }

        // Check if callee already has an active call
        $activeCall = P2PCall::where(function ($q) use ($callee) {
                $q->where('caller_id', $callee->id)
                  ->orWhere('callee_id', $callee->id);
            })
            ->whereIn('status', ['ringing', 'in_progress'])
            ->first();

        if ($activeCall) {
            return response()->json(['error' => 'User is busy on another call'], 409);
        }

        $call = P2PCall::create([
            'caller_id'   => $caller->id,
            'callee_id'   => $callee->id,
            'business_id' => $caller->business_id,
            'status'      => 'ringing',
            'started_at'  => now(),
        ]);

        $call->load(['caller', 'callee']);

        // Broadcast to callee
        broadcast(new IncomingCall($call));

        return response()->json([
            'call_id' => $call->uuid,
            'callee'  => [
                'id'    => $callee->id,
                'uuid'  => $callee->uuid,
                'name'  => $callee->name,
                'photo' => $callee->profile_photo_url,
            ],
        ]);
    }

    /**
     * Accept an incoming call
     */
    public function acceptCall($callUuid)
    {
        $call = P2PCall::where('uuid', $callUuid)
            ->where('callee_id', Auth::id())
            ->where('status', 'ringing')
            ->firstOrFail();

        $call->update([
            'status'      => 'in_progress',
            'answered_at' => now(),
        ]);

        $call->load(['caller', 'callee']);

        // Notify the caller
        broadcast(new CallAccepted($call));

        return response()->json(['status' => 'accepted']);
    }

    /**
     * Reject an incoming call
     */
    public function rejectCall($callUuid)
    {
        $call = P2PCall::where('uuid', $callUuid)
            ->where('callee_id', Auth::id())
            ->where('status', 'ringing')
            ->firstOrFail();

        $call->update([
            'status'     => 'rejected',
            'end_reason' => 'rejected',
            'ended_at'   => now(),
        ]);

        // Notify the caller
        broadcast(new CallRejected($call));

        return response()->json(['status' => 'rejected']);
    }

    /**
     * Cancel an outgoing call (caller cancels before answer)
     */
    public function cancelCall($callUuid)
    {
        $call = P2PCall::where('uuid', $callUuid)
            ->where('caller_id', Auth::id())
            ->where('status', 'ringing')
            ->firstOrFail();

        $call->update([
            'status'     => 'cancelled',
            'end_reason' => 'cancelled',
            'ended_at'   => now(),
        ]);

        // Notify the callee
        broadcast(new CallEnded($call, $call->callee_id));

        return response()->json(['status' => 'cancelled']);
    }

    /**
     * End an active call
     */
    public function endCall($callUuid)
    {
        $user = Auth::user();

        $call = P2PCall::where('uuid', $callUuid)
            ->where(function ($q) use ($user) {
                $q->where('caller_id', $user->id)
                  ->orWhere('callee_id', $user->id);
            })
            ->whereIn('status', ['ringing', 'in_progress'])
            ->firstOrFail();

        $endReason = $call->status === 'ringing' ? 'missed' : 'normal';

        $call->update([
            'status'     => $call->status === 'ringing' ? 'missed' : 'completed',
            'end_reason' => $endReason,
            'ended_at'   => now(),
        ]);

        // Notify the other participant
        $otherUserId = $call->caller_id === $user->id ? $call->callee_id : $call->caller_id;
        broadcast(new CallEnded($call, $otherUserId));

        return response()->json([
            'status'   => 'ended',
            'duration' => $call->duration,
        ]);
    }

    /**
     * Relay WebRTC signaling data (offer, answer, ICE candidate)
     */
    public function signal(Request $request, $callUuid)
    {
        $request->validate([
            'type' => 'required|in:offer,answer,candidate',
            'data' => 'required|array',
        ]);

        $user = Auth::user();

        $call = P2PCall::where('uuid', $callUuid)
            ->where(function ($q) use ($user) {
                $q->where('caller_id', $user->id)
                  ->orWhere('callee_id', $user->id);
            })
            ->whereIn('status', ['ringing', 'in_progress'])
            ->firstOrFail();

        // Send signal to the other participant
        $targetUserId = $call->caller_id === $user->id ? $call->callee_id : $call->caller_id;

        broadcast(new WebRTCSignal(
            $call->uuid,
            $targetUserId,
            $request->type,
            $request->data,
        ));

        return response()->json(['status' => 'sent']);
    }

    /**
     * Get call history for the current user
     */
    public function callHistory()
    {
        $user = Auth::user();

        $calls = P2PCall::forUser($user->id)
            ->forBusiness($user->business_id)
            ->with(['caller', 'callee'])
            ->latest('created_at')
            ->paginate(20);

        return view('calls.history', compact('calls', 'user'));
    }

    /**
     * Get online users in the same business (AJAX endpoint)
     */
    public function onlineUsers()
    {
        $user = Auth::user();

        // Get all users in the same business (excluding self)
        $users = User::where('business_id', $user->business_id)
            ->where('id', '!=', $user->id)
            ->where('status', 'active')
            ->select('id', 'uuid', 'name', 'profile_photo_path')
            ->get()
            ->map(function ($u) {
                return [
                    'id'    => $u->id,
                    'uuid'  => $u->uuid,
                    'name'  => $u->name,
                    'photo' => $u->profile_photo_url,
                ];
            });

        return response()->json($users);
    }
}
