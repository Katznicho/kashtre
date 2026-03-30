<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class BroadcastAuthController extends Controller
{
    public function __invoke(Request $request): JsonResponse|SymfonyResponse
    {
        try {
            $response = Broadcast::auth($request);

            if ($response instanceof SymfonyResponse) {
                return $response;
            }

            if (is_array($response)) {
                return response()->json($response);
            }

            if (is_string($response) && $response !== '') {
                return response($response, 200, ['Content-Type' => 'application/json']);
            }

            Log::warning('Broadcast auth returned an empty response.', [
                'user_id' => optional($request->user())->id,
                'channel_name' => $request->input('channel_name'),
                'socket_id' => $request->input('socket_id'),
            ]);

            return response()->json([
                'message' => 'Broadcast authorization returned an empty response.',
            ], 500);
        } catch (AccessDeniedHttpException $e) {
            return response()->json([
                'message' => 'You are not authorized to join this channel.',
            ], 403);
        } catch (Throwable $e) {
            Log::error('Broadcast auth failed.', [
                'message' => $e->getMessage(),
                'user_id' => optional($request->user())->id,
                'channel_name' => $request->input('channel_name'),
                'socket_id' => $request->input('socket_id'),
            ]);

            return response()->json([
                'message' => 'Broadcast authorization failed.',
            ], 500);
        }
    }
}
