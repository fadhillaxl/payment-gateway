<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VendingController;
use App\Http\Controllers\MqttTestController;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Payment Routes
Route::get('/payment', [PaymentController::class, 'index'])->name('payment');
Route::post('/payment/token', [PaymentController::class, 'getToken'])->name('payment.token');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/pending', [PaymentController::class, 'pending'])->name('payment.pending');
Route::get('/payment/error', [PaymentController::class, 'error'])->name('payment.error');
Route::post('/payment/notification', [PaymentController::class, 'notification'])->name('payment.notification');
Route::post('/payment/update-status', [PaymentController::class, 'updateTransactionStatus'])->name('payment.update-status');
Route::get('/payment/status', [PaymentController::class, 'getRelayStatus']);

// Vending Routes
Route::get('/payment/vend', [VendingController::class, 'handleToken'])->name('vending.order');
Route::post('/payment/vend/order', [VendingController::class, 'sendOrder'])->name('vending.sendOrder');
Route::post('/payment/vend/notification', [VendingController::class, 'handleNotification'])->name('vending.notification');
Route::get('/payment/vend/mqtt-status', [VendingController::class, 'getMqttStatus'])->name('vending.mqtt.status');

// MQTT Test Routes (Consider adding auth middleware if this is sensitive)
Route::prefix('admin/mqtt-test')->group(function () {
    Route::get('/', [MqttTestController::class, 'index'])->name('admin.mqtt.test.index');
    Route::post('/submit', [MqttTestController::class, 'testConnection'])->name('admin.mqtt.test.submit');
    Route::post('/publish', [MqttTestController::class, 'publish'])->name('admin.mqtt.test.publish');
    Route::post('/subscribe', [MqttTestController::class, 'subscribe'])->name('admin.mqtt.test.subscribe');
    Route::post('/unsubscribe', [MqttTestController::class, 'unsubscribe'])->name('admin.mqtt.test.unsubscribe');
    Route::post('/disconnect', [MqttTestController::class, 'disconnect'])->name('admin.mqtt.test.disconnect');
    Route::get('/status', [MqttTestController::class, 'status'])->name('admin.mqtt.test.status');
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
