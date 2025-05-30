<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\VendingMachine;
use App\Services\MqttService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Midtrans\Config;
use Midtrans\Snap;

class VendingController extends Controller
{
    public function __construct()
    {
        // Set Midtrans Configuration
        Config::$serverKey = config('services.midtrans.server_key');
        Config::$isProduction = config('services.midtrans.is_production');
        Config::$isSanitized = config('services.midtrans.sanitize');
        Config::$is3ds = config('services.midtrans.3ds');
    }

    public function handleToken(Request $request, MqttService $mqtt, $token)
    {
        $machine = VendingMachine::where('token', $token)->firstOrFail();

        // Try to establish MQTT connection
        try {
            $mqtt->connectIfNeeded();
            \Log::info('MQTT connection established for vending machine', [
                'machine_id' => $machine->id,
                'token' => $machine->token
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to establish MQTT connection', [
                'error' => $e->getMessage(),
                'machine_id' => $machine->id,
                'token' => $machine->token
            ]);
        }

        return Inertia::render('Vending', [
            'machine' => $machine,
            'clientKey' => config('services.midtrans.client_key')
        ]);
    }

    public function sendOrder(Request $request, MqttService $mqtt)
    {
        dd($request->all());
        try {
            $validated = $request->validate([
                'token' => 'required|string|exists:vending_machines,token',
                'items' => 'required|array',
                'items.*.name' => 'required|string',
                'items.*.quantity' => 'required|integer|min:1',
                'items.*.price' => 'required|numeric|min:0',
                'amount' => 'required|numeric|min:1000'
            ]);

            $machine = VendingMachine::where('token', $validated['token'])->firstOrFail();

            // Create Midtrans transaction
            $transaction_details = [
                'order_id' => 'ORDER-' . time(),
                'gross_amount' => (int) $validated['amount']
            ];

            $customer_details = [
                'first_name' => 'Customer',
                'email' => 'customer@example.com',
                'phone' => '08123456789'
            ];

            $item_details = array_map(function($item) {
                return [
                    'id' => 'ITEM-' . time() . '-' . $item['name'],
                    'price' => (int) $item['price'],
                    'quantity' => $item['quantity'],
                    'name' => $item['name']
                ];
            }, $validated['items']);

            $transaction_data = [
                'transaction_details' => $transaction_details,
                'customer_details' => $customer_details,
                'item_details' => $item_details,
            ];

            // Get Snap Token
            $snapToken = Snap::getSnapToken($transaction_data);

            // Create order record
            $order = Order::create([
                'order_id' => $transaction_details['order_id'],
                'machine_token' => $validated['token'],
                'item' => json_encode($validated['items']), // Store all items as JSON
                'quantity' => array_sum(array_column($validated['items'], 'quantity')), // Total quantity
                'amount' => $validated['amount'],
                'snap_token' => $snapToken,
                'transaction_status' => 'pending'
            ]);

            // Store order in session for MQTT processing after payment
            session([
                'pending_order' => [
                    'machine_token' => $validated['token'],
                    'items' => $validated['items'],
                    'topic' => $machine->topic,
                    'order_id' => $order->id
                ]
            ]);

            return response()->json([
                'snap_token' => $snapToken,
                'order_id' => $transaction_details['order_id']
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Order processing error: ' . $e->getMessage());
            \Log::error('Stack trace: ' . $e->getTraceAsString());
            return response()->json([
                'message' => 'Failed to process order',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function handleNotification(Request $request, MqttService $mqtt)
    {
        \Log::info('VendingController: handleNotification received.', ['payload' => $request->all()]);

        $payload = $request->all();
        
        // Verify signature
        $signatureKey = $payload['signature_key'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $serverKey = config('services.midtrans.server_key');

        if (!$signatureKey || !$orderId || !$statusCode || !$grossAmount) {
            \Log::error('VendingController: Missing one or more required fields for signature verification.', $payload);
            return response()->json(['message' => 'Invalid payload for signature verification'], 400);
        }

        $mySignatureKey = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);
        \Log::info('VendingController: Signature verification details.', ['received_signature' => $signatureKey, 'calculated_signature' => $mySignatureKey, 'order_id' => $orderId]);

        if ($signatureKey !== $mySignatureKey) {
            \Log::warning('VendingController: Invalid signature.', ['payload' => $payload]);
            return response()->json(['message' => 'Invalid signature'], 400);
        }
        \Log::info('VendingController: Signature verified successfully.');

        // Find the order
        $order = Order::where('order_id', $orderId)->first();
        if (!$order) {
            \Log::warning('VendingController: Order not found.', ['order_id' => $orderId]);
            return response()->json(['message' => 'Order not found'], 404);
        }

        // Process order based on transaction status
        $transactionStatus = $payload['transaction_status'] ?? null;
        \Log::info('VendingController: Transaction status.', ['status' => $transactionStatus]);

        if ($transactionStatus === 'settlement' || $transactionStatus === 'capture') {
            \Log::info('VendingController: Payment successful (settlement or capture). Attempting to publish to MQTT.');
            try {
                $mqttPayload = [
                    'item' => $order->item,
                    'quantity' => $order->quantity,
                    'machine_token' => $order->machine_token,
                    'order_id' => $order->order_id
                ];
                \Log::info('VendingController: Publishing to MQTT topic orders/', ['payload' => $mqttPayload]);
                $mqtt->publish('orders/', $mqttPayload);
                \Log::info('VendingController: Successfully published to MQTT topic orders/.');

                // Update order status
                $order->update([
                    'transaction_status' => $transactionStatus,
                    'payment_type' => $payload['payment_type'] ?? null,
                    'paid_at' => now()
                ]);

                // Clear pending order from session
                session()->forget('pending_order');
                \Log::info('VendingController: Cleared pending_order from session.');

                return response()->json(['message' => 'Order processed successfully']);
            } catch (\Exception $e) {
                \Log::error('VendingController: MQTT Publish Exception.', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                return response()->json(['message' => 'Payment successful, but MQTT publish failed.'], 500);
            }
        } else {
            // Update order status for other transaction statuses
            $order->update([
                'transaction_status' => $transactionStatus,
                'payment_type' => $payload['payment_type'] ?? null
            ]);
            
            \Log::info('VendingController: Payment status not settlement or capture. Notification acknowledged.', ['status' => $transactionStatus]);
            return response()->json(['message' => 'Payment status not settlement or capture. Notification acknowledged.']);
        }
    }

    public function getMqttStatus(MqttService $mqtt, $token)
    {
        // Verify the token exists
        $machine = VendingMachine::where('token', $token)->firstOrFail();
        
        $status = $mqtt->checkConnectionStatus();
        return response()->json($status);
    }

    public function menuOrder(MqttService $mqtt, $token)
    {
        // dd($token);
        $machine = VendingMachine::where('token', $token)->firstOrFail();
        dd($machine);
        return Inertia::render('Vending', [
            'machine' => $machine,
            'clientKey' => config('services.midtrans.client_key')
        ]);
    }
    
} 