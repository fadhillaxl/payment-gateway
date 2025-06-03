<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\VendingController;
use App\Http\Controllers\MqttTestController;

// Public routes
Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

// Dashboard route with proper middleware
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});

// Payment Routes
Route::get('/payment', [PaymentController::class, 'index'])->name('payment');
Route::post('/payment/token', [PaymentController::class, 'getToken'])->name('payment.token');
Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
Route::get('/payment/pending', [PaymentController::class, 'pending'])->name('payment.pending');
Route::get('/payment/error', [PaymentController::class, 'error'])->name('payment.error');
Route::post('/payment/notification', [PaymentController::class, 'notification'])->name('payment.notification');
Route::get('/payment/status', [PaymentController::class, 'getRelayStatus']);

// Vending Routes
Route::get('/payment/vend/{token}', [VendingController::class, 'handleToken'])->name('vending.order');
Route::post('/payment/vend/order', [VendingController::class, 'sendOrder'])->name('vending.sendOrder');
Route::post('/payment/vend/notification', [VendingController::class, 'handleNotification'])->name('vending.notification');
Route::get('/payment/vend/{token}/mqtt-status', [VendingController::class, 'getMqttStatus'])->name('vending.mqtt.status');

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

// Dashboard Routes
Route::middleware(['auth', 'verified'])->prefix('dashboard')->name('dashboard.')->group(function () {
    // Menu Items
    Route::resource('menu-items', \App\Http\Controllers\Admin\MenuItemController::class);
    
    // MQTT Configs
    Route::resource('mqtt-configs', \App\Http\Controllers\Admin\MqttConfigController::class);

    // MQTT Test Routes
    Route::prefix('mqtt-test')->name('mqtt.test.')->group(function () {
        Route::get('/', [MqttTestController::class, 'index'])->name('index');
        Route::post('/submit', [MqttTestController::class, 'testConnection'])->name('submit');
        Route::post('/publish', [MqttTestController::class, 'publish'])->name('publish');
        Route::post('/subscribe', [MqttTestController::class, 'subscribe'])->name('subscribe');
        Route::post('/unsubscribe', [MqttTestController::class, 'unsubscribe'])->name('unsubscribe');
        Route::post('/disconnect', [MqttTestController::class, 'disconnect'])->name('disconnect');
        Route::get('/status', [MqttTestController::class, 'status'])->name('status');
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
