<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;
use App\Services\MqttService;
use App\Models\Order;

class PaymentController extends Controller
{
    protected MqttService $mqttService;

    public function __construct(MqttService $mqttService)
    {
        $this->mqttService = $mqttService;
        // Set Midtrans configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$clientKey = config('services.midtrans.client_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.is_sanitized');
        Config::$is3ds = config('services.midtrans.is_3ds');
    }

    public function index()
    {
        return Inertia::render('Payment', [
            'clientKey' => config('services.midtrans.client_key')
        ]);
    }

    public function getToken(Request $request)
    {
        try {
            $amount = $request->input('amount', 100000);
            $itemId = $request->input('item_id');
            $itemName = $request->input('item_name', 'Payment for Service');
            $vendingToken = $request->input('vending_token'); // Get vending token from request

            \Log::info('Attempting to get Midtrans token with amount: ' . $amount);
            \Log::info('Midtrans config:', [
                'server_key' => config('services.midtrans.server_key'),
                'client_key' => config('services.midtrans.client_key'),
                'is_production' => config('services.midtrans.is_production'),
            ]);

            $params = [
                'transaction_details' => [
                    'order_id' => 'ORDER-' . time(),
                    'gross_amount' => $amount,
                ],
                'customer_details' => [
                    'first_name' => 'John',
                    'last_name' => 'Doe',
                    'email' => 'john.doe@example.com',
                    'phone' => '08123456789',
                ],
                'item_details' => [
                    [
                        'id' => $itemId ? 'ITEM-' . $itemId : 'ITEM-' . time(),
                        'price' => $amount,
                        'quantity' => 1,
                        'name' => $itemName,
                    ]
                ],
                'custom_field1' => $vendingToken, // Store vending token in custom field
            ];

            \Log::info('Sending request to Midtrans with params:', $params);

            $snapToken = Snap::getSnapToken($params);
            \Log::info('Successfully received snap token');

            return response()->json(['snapToken' => $snapToken]);
        } catch (\Exception $e) {
            \Log::error('Midtrans token request failed: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'error' => $e->getMessage(),
                'details' => 'Please check the server logs for more information'
            ], 500);
        }
    }

    public function sendMqttMessage(MqttService $mqtt, $topic, $data)
    {
        try {
            // Ensure MQTT connection is established
            $mqtt->connectIfNeeded();

            // Ensure data is an array
            $payload = is_array($data) ? $data : ['message' => $data];

            // Publish message to MQTT topic
            $mqtt->publish($topic, $payload);

            \Log::info('MQTT message sent successfully', [
                'topic' => $topic,
                'payload' => $payload
            ]);

            return true;
        } catch (\Exception $e) {
            \Log::error('Failed to send MQTT message', [
                'error' => $e->getMessage(),
                'topic' => $topic,
                'data' => $data
            ]);
            return false;
        }
    }

    public function success(Request $request)
    {   
        $order_id = $request->input('order_id');
        $order = Order::where('order_id', $order_id)->first();
        // dd($order);
        $status = $this->sendMqttMessage($this->mqttService, 'order/', [
            'order_id' => $order_id,
            'order' => $order->item,
            'status' => 'success'
        ]);
        // dd($status);
        return Inertia::render('PaymentSuccess', [
            'order_id' => $order_id,
            'order' => $order,
            'status' => $status
        ]);
    }

    public function pending()
    {
        return Inertia::render('PaymentPending');
    }

    public function error()
    {
        return Inertia::render('PaymentError');
    }

    private function controlRelay($action)
    {
        try {
            // Replace with your IoT device's API endpoint
            $iotEndpoint = config('services.iot.endpoint', 'http://your-iot-device-ip/relay');
            
            $response = Http::post($iotEndpoint, [
                'action' => $action,
                'timestamp' => now()->toDateTimeString(),
            ]);

            if ($response->successful()) {
                \Log::info("Relay {$action} command sent successfully");
                return true;
            } else {
                \Log::error("Failed to send relay {$action} command: " . $response->body());
                return false;
            }
        } catch (\Exception $e) {
            \Log::error("Error controlling relay: " . $e->getMessage());
            return false;
        }
    }

    public function notification(Request $request)
    {
        \Log::info('Received Midtrans notification', [
            'request_data' => $request->all()
        ]);
        
        try {
            $notif = new \Midtrans\Notification();
            
            $transaction = $notif->transaction_status;
            $type = $notif->payment_type;
            $order_id = $notif->order_id;
            $fraud = $notif->fraud_status;
            $gross_amount = $notif->gross_amount;
            $item_details = $notif->item_details;
            
            \Log::info('Processed notification data', [
                'transaction' => $transaction,
                'type' => $type,
                'order_id' => $order_id,
                'fraud' => $fraud,
                'gross_amount' => $gross_amount,
                'item_details' => $item_details
            ]);
        } catch (\Exception $e) {
            \Log::warning('Failed to get transaction status from Midtrans, using request data instead', [
                'error' => $e->getMessage(),
                'request_data' => $request->all()
            ]);
            
            // Use request data directly if Midtrans API call fails
            $transaction = $request->input('transaction_status');
            $type = $request->input('payment_type');
            $order_id = $request->input('order_id');
            $fraud = $request->input('fraud_status');
            $gross_amount = $request->input('gross_amount');
            $item_details = $request->input('item_details');
        }
        
        // Get vending token from custom fields if available
        $vending_token = $request->input('custom_field1') ?? null;
        $is_success = in_array($transaction, ['capture', 'settlement']) && 
                     ($type != 'credit_card' || $fraud != 'challenge');

        \Log::info('Determined transaction status', [
            'vending_token' => $vending_token,
            'is_success' => $is_success,
            'transaction' => $transaction
        ]);

        // Find and update the order
        $order = Order::where('order_id', $order_id)->first();
        if (!$order) {
            \Log::error('Order not found in database', ['order_id' => $order_id]);
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        // Update order status
        $order->update([
            'transaction_status' => $transaction,
            'payment_type' => $type,
            'paid_at' => $is_success ? now() : null
        ]);

        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    \Log::info('Processing credit card challenge');
                    $this->publishOrderToMqtt($order_id, $gross_amount, $item_details, 'challenge', $vending_token, false);
                } else {
                    \Log::info('Processing successful credit card payment');
                    $this->controlRelay('on');
                    $this->publishOrderToMqtt($order_id, $gross_amount, $item_details, 'success', $vending_token, true);
                }
            }
        } else if ($transaction == 'settlement') {
            \Log::info('Processing settlement');
            $this->controlRelay('on');
            $this->publishOrderToMqtt($order_id, $gross_amount, $item_details, 'settlement', $vending_token, true);
        } else if ($transaction == 'pending') {
            \Log::info('Processing pending payment');
            $this->publishOrderToMqtt($order_id, $gross_amount, $item_details, 'pending', $vending_token, false);
        } else if ($transaction == 'deny') {
            \Log::info('Processing denied payment');
            $this->publishOrderToMqtt($order_id, $gross_amount, $item_details, 'denied', $vending_token, false);
        } else if ($transaction == 'expire') {
            \Log::info('Processing expired payment');
            $this->publishOrderToMqtt($order_id, $gross_amount, $item_details, 'expired', $vending_token, false);
        } else if ($transaction == 'cancel') {
            \Log::info('Processing cancelled payment');
            $this->publishOrderToMqtt($order_id, $gross_amount, $item_details, 'cancelled', $vending_token, false);
        }

        return response()->json(['status' => 'success']);
    }

    private function publishOrderToMqtt($order_id, $gross_amount, $item_details, $status, $vending_token = null, $is_success = false)
    {
        try {
            \Log::info("Starting to publish order to MQTT", [
                'order_id' => $order_id,
                'status' => $status,
                'vending_token' => $vending_token
            ]);

            // Ensure MQTT connection is active
            $this->mqttService->connectIfNeeded();

            $orderData = [
                'order_id' => $order_id,
                'amount' => $gross_amount,
                'items' => $item_details,
                'status' => $status,
                'vending_token' => $vending_token,
                'is_success' => $is_success,
                'timestamp' => now()->toIso8601String(),
            ];

            // Publish to specific order topic
            $specificTopic = 'orders/';
            \Log::info("Preparing to publish to specific order topic", [
                'topic' => $specificTopic,
                'data' => $orderData
            ]);

            $this->mqttService->publish(
                $specificTopic,
                $orderData,
                1, // QoS 1 for at-least-once delivery
                false // Don't retain the message
            );

            \Log::info("Successfully published to specific order topic", [
                'topic' => $specificTopic
            ]);

            // Publish to general orders topic
            $generalTopic = 'orders/all';
            \Log::info("Preparing to publish to general orders topic", [
                'topic' => $generalTopic,
                'data' => $orderData
            ]);

            $this->mqttService->publish(
                $generalTopic,
                $orderData,
                1,
                false
            );

            \Log::info("Successfully published to general orders topic", [
                'topic' => $generalTopic
            ]);

            return true;

        } catch (\Exception $e) {
            \Log::error("Failed to publish order to MQTT", [
                'order_id' => $order_id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    public function updateTransactionStatus(Request $request)
    {
        try {
            $orderId = $request->input('order_id');
            $transactionStatus = $request->input('transaction_status');
            $paymentType = $request->input('payment_type');

            $order = Order::where('order_id', $orderId)->first();

            if (!$order) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Order not found'
                ], 404);
            }

            $isSuccess = in_array($transactionStatus, ['capture', 'settlement']);

            $order->update([
                'transaction_status' => $transactionStatus,
                'payment_type' => $paymentType,
                'paid_at' => $isSuccess ? now() : null
            ]);

            return response()->json([
                'status' => 'success',
                'message' => 'Transaction status updated successfully',
                'data' => [
                    'order_id' => $order->order_id,
                    'transaction_status' => $order->transaction_status,
                    'payment_type' => $order->payment_type,
                    'paid_at' => $order->paid_at
                ]
            ]);

        } catch (\Exception $e) {
            \Log::error('Failed to update transaction status: ' . $e->getMessage());
            return response()->json([
                'status' => 'error',
                'message' => 'Failed to update transaction status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}