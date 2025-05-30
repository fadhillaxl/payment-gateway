<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Log;

class TestMqttConnection extends Command
{
    protected $signature = 'mqtt:test-connection';
    protected $description = 'Test MQTT connection with detailed logging';

    public function handle()
    {
        $this->info('Starting MQTT connection test...');
        
        $config = config('mqtt');
        
        $this->info('Using configuration:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['Host', $config['host']],
                ['Port', $config['port']],
                ['Username', $config['username']],
                ['Client ID', $config['client_id'] ?? 'test_client_' . uniqid()],
                ['Use TLS', $config['use_tls'] ? 'Yes' : 'No'],
            ]
        );

        try {
            // Create connection settings
            $settings = (new ConnectionSettings)
                ->setUsername($config['username'])
                ->setPassword($config['password'])
                ->setKeepAliveInterval(60)
                ->setConnectTimeout(5)
                ->setSocketTimeout(5);

            $clientId = $config['client_id'] ?? 'test_client_' . uniqid();
            
            $this->info("Attempting to connect with client ID: {$clientId}");
            
            $client = new MqttClient(
                $config['host'],
                (int)$config['port'],
                $clientId,
                MqttClient::MQTT_3_1_1
            );

            $this->info('Connecting to MQTT broker...');
            $client->connect($settings, true);
            
            if ($client->isConnected()) {
                $this->info('Successfully connected to MQTT broker!');
                
                // Test publishing a message
                $testTopic = 'vending/test';
                $testMessage = json_encode([
                    'test' => true,
                    'timestamp' => now()->toIso8601String()
                ]);
                
                $this->info("Publishing test message to topic: {$testTopic}");
                $client->publish($testTopic, $testMessage, 0, false);
                
                // Test subscribing
                $this->info("Subscribing to topic: {$testTopic}");
                $client->subscribe($testTopic, function ($topic, $message) {
                    $this->info("Received message on topic {$topic}: {$message}");
                }, 0);
                
                // Wait for a few seconds to receive any messages
                $this->info('Waiting for messages (5 seconds)...');
                sleep(5);
                
                $client->disconnect();
                $this->info('Test completed successfully!');
            } else {
                $this->error('Failed to connect to MQTT broker.');
            }
        } catch (\Exception $e) {
            $this->error('Connection test failed:');
            $this->error($e->getMessage());
            $this->error('Error code: ' . $e->getCode());
            
            Log::error('MQTT Connection Test Failed', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'config' => [
                    'host' => $config['host'],
                    'port' => $config['port'],
                    'username' => $config['username'],
                    'client_id' => $clientId ?? null
                ]
            ]);
        }
    }
} 