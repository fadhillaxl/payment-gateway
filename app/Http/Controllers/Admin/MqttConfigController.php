<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\MqttConfig;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MqttConfigController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $configs = MqttConfig::latest()->get();
        return Inertia::render('Admin/MqttConfigs/Index', [
            'configs' => $configs
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/MqttConfigs/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        MqttConfig::create($validated);

        return redirect()->route('dashboard.mqtt-configs.index')
            ->with('success', 'MQTT configuration created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MqttConfig $mqttConfig)
    {
        return Inertia::render('Admin/MqttConfigs/Edit', [
            'config' => $mqttConfig
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MqttConfig $mqttConfig)
    {
        $validated = $request->validate([
            'host' => 'required|string|max:255',
            'port' => 'required|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'client_id' => 'nullable|string|max:255',
            'is_active' => 'boolean'
        ]);

        $mqttConfig->update($validated);

        return redirect()->route('dashboard.mqtt-configs.index')
            ->with('success', 'MQTT configuration updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(MqttConfig $mqttConfig)
    {
        $mqttConfig->delete();

        return redirect()->route('dashboard.mqtt-configs.index')
            ->with('success', 'MQTT configuration deleted successfully.');
    }
}
