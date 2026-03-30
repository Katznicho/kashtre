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
    protected function shouldExposeError(Request $request): bool
    {
        return str_starts_with($request->getHost(), 'staging.');
    }

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

            $payload = [
                'message' => 'Broadcast authorization returned an empty response.',
            ];

            if ($this->shouldExposeError($request)) {
                $payload['error'] = 'Empty authorization payload returned by Broadcast::auth().';
            }

            return response()->json($payload, 500);
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

            $payload = [
                'message' => 'Broadcast authorization failed.',
            ];

            if ($this->shouldExposeError($request)) {
                $payload['error'] = $e->getMessage();
                $payload['exception'] = class_basename($e);
            }

            return response()->json($payload, 500);
        }
    }
}
