<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Midtrans\Config;
use Midtrans\Snap;
use Inertia\Inertia;
use Illuminate\Support\Facades\Http;

class PaymentController extends Controller
{
    public function __construct()
    {
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

    public function success()
    {
        return Inertia::render('PaymentSuccess');
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
        $notif = new \Midtrans\Notification();
        
        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;

        if ($transaction == 'capture') {
            if ($type == 'credit_card') {
                if ($fraud == 'challenge') {
                    // TODO: Set payment status in merchant's database to 'challenge'
                } else {
                    // TODO: Set payment status in merchant's database to 'success'
                    $this->controlRelay('on'); // Turn on relay after successful payment
                }
            }
        } else if ($transaction == 'settlement') {
            // TODO: Set payment status in merchant's database to 'success'
            $this->controlRelay('on'); // Turn on relay after settlement
        } else if ($transaction == 'pending') {
            // TODO: Set payment status in merchant's database to 'pending'
        } else if ($transaction == 'deny') {
            // TODO: Set payment status in merchant's database to 'failed'
        } else if ($transaction == 'expire') {
            // TODO: Set payment status in merchant's database to 'expired'
        } else if ($transaction == 'cancel') {
            // TODO: Set payment status in merchant's database to 'failed'
        }

        return response()->json(['status' => 'success']);
    }
}