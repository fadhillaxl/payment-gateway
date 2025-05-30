<?php

namespace App\Http\Controllers;

use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Inertia\Inertia;

class MqttTestController extends Controller
{
    protected MqttService $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
    }

    /**
     * Display the MQTT test page.
     */
    public function index()
    {
        return Inertia::render('Admin/MqttTest');
    }

    /**
     * Handle the MQTT connection test submission.
     */
    public function testConnection(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'use_tls' => 'boolean',
            'client_id' => 'nullable|string|max:255',
            'connect_timeout' => 'nullable|integer|min:1',
            'socket_timeout' => 'nullable|integer|min:1',
            'keep_alive_interval' => 'nullable|integer|min:1',
            'clean_session' => 'boolean',
        ]);

        if ($validator->fails()) {
            Log::warning('[MqttTestController] Validation failed for manual test.', ['errors' => $validator->errors()->toArray()]);
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        $params = $validator->validated();
        
        Log::info('[MqttTestController] Manual MQTT test requested with params:', $params);

        $result = $this->mqttService->testManualConnection($params);

        return response()->json($result);
    }

    /**
     * Handle MQTT publish request.
     */
    public function publish(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:255',
            'message' => 'required|string',
            'qos' => 'integer|min:0|max:2',
            'retain' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->mqttService->publish(
                $request->input('topic'),
                ['message' => $request->input('message')],
                (int)$request->input('qos', 0),
                (bool)$request->input('retain', false)
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Message published successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('[MqttTestController] Publish failed:', [
                'error' => $e->getMessage(),
                'topic' => $request->input('topic')
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to publish message: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle MQTT subscribe request.
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:255',
            'qos' => 'integer|min:0|max:2',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->mqttService->subscribe(
                $request->input('topic'),
                (int)$request->input('qos', 0)
            );

            return response()->json([
                'status' => 'success',
                'message' => 'Subscribed to topic successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('[MqttTestController] Subscribe failed:', [
                'error' => $e->getMessage(),
                'topic' => $request->input('topic')
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to subscribe to topic: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle MQTT unsubscribe request.
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'topic' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'validation_error',
                'message' => 'Validation failed.',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $this->mqttService->unsubscribe($request->input('topic'));

            return response()->json([
                'status' => 'success',
                'message' => 'Unsubscribed from topic successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('[MqttTestController] Unsubscribe failed:', [
                'error' => $e->getMessage(),
                'topic' => $request->input('topic')
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to unsubscribe from topic: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle MQTT disconnect request.
     */
    public function disconnect()
    {
        try {
            $this->mqttService->disconnect();

            return response()->json([
                'status' => 'success',
                'message' => 'Disconnected from MQTT broker successfully'
            ]);
        } catch (\Exception $e) {
            Log::error('[MqttTestController] Disconnect failed:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to disconnect: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get current MQTT connection status.
     */
    public function status()
    {
        try {
            $status = $this->mqttService->checkConnectionStatus();
            return response()->json($status);
        } catch (\Exception $e) {
            Log::error('[MqttTestController] Status check failed:', [
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'status' => 'error',
                'message' => 'Failed to check status: ' . $e->getMessage()
            ], 500);
        }
    }
} 