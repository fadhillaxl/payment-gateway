<?php

namespace App\Services;

use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use PhpMqtt\Client\Exceptions\MqttClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class MqttService
{
    private ?MqttClient $client = null;
    private array $config;
    private ?string $connectionId = null;
    private const CONNECTION_KEY = 'mqtt_active_connection';
    private const CONNECTION_TTL = 3600; // 1 hour

    public function __construct()
    {
        // Store config
        $this->config = config('mqtt');
        
        // Try to restore existing connection first
        $this->connectionId = Cache::get(self::CONNECTION_KEY);
        if ($this->connectionId) {
            $this->restoreConnection();
        } else {
            // If no existing connection, try to establish a new one
            try {
                $this->connectIfNeeded();
            } catch (\Exception $e) {
                Log::error('[MqttService] Failed to establish initial MQTT connection', [
                    'error' => $e->getMessage()
                ]);
            }
        }
    }

    private function restoreConnection(): void
    {
        $connectionInfo = Cache::get($this->connectionId);
        if (!$connectionInfo) {
            Cache::forget(self::CONNECTION_KEY);
            $this->connectionId = null;
            return;
        }

        try {
            $settings = $this->createConnectionSettings($connectionInfo);
            
            $this->client = new MqttClient(
                $connectionInfo['host'],
                (int)$connectionInfo['port'],
                $connectionInfo['client_id'],
                MqttClient::MQTT_3_1_1
            );

            $this->client->connect($settings, $connectionInfo['clean_session'] ?? true);
            
            // Resubscribe to any stored subscriptions
            foreach ($connectionInfo['subscriptions'] ?? [] as $topic) {
                $this->client->subscribe($topic, function (string $topic, string $message) {
                    Log::info("[MqttService] Message received on restored subscription", [
                        'topic' => $topic,
                        'message' => $message
                    ]);
                }, 0);
            }

            Log::info('[MqttService] Successfully restored MQTT connection', [
                'client_id' => $connectionInfo['client_id'],
                'host' => $connectionInfo['host']
            ]);
        } catch (\Exception $e) {
            Log::error('[MqttService] Failed to restore MQTT connection', [
                'error' => $e->getMessage()
            ]);
            Cache::forget(self::CONNECTION_KEY);
            Cache::forget($this->connectionId);
            $this->connectionId = null;
            $this->client = null;
        }
    }

    private function createConnectionSettings(array $config): ConnectionSettings
    {
        $settings = (new ConnectionSettings)
            ->setConnectTimeout((int)($config['connect_timeout'] ?? $this->config['connect_timeout']))
            ->setKeepAliveInterval((int)($config['keep_alive_interval'] ?? $this->config['keep_alive_interval']))
            ->setSocketTimeout((int)($config['socket_timeout'] ?? $this->config['socket_timeout']))
            ->setLastWillTopic('vending/status')
            ->setLastWillMessage('client disconnected')
            ->setLastWillQualityOfService(1);

        if ((bool)($config['use_tls'] ?? $this->config['use_tls'])) {
            $settings->setUseTls(true)
                ->setCaFile($config['tls_cafile'] ?? $this->config['tls_cafile'])
                ->setClientCertificateFile($config['tls_certfile'] ?? $this->config['tls_certfile'])
                ->setClientKeyFile($config['tls_keyfile'] ?? $this->config['tls_keyfile'])
                ->setVerifyPeer((bool)($config['tls_verify_peer'] ?? $this->config['tls_verify_peer']));
        }

        if (!empty($config['username'] ?? $this->config['username'])) {
            $settings->setUsername($config['username'] ?? $this->config['username']);
        }
        if (!empty($config['password'] ?? $this->config['password'])) {
            $settings->setPassword($config['password'] ?? $this->config['password']);
        }

        return $settings;
    }

    private function storeConnection(array $connectionInfo): void
    {
        $this->connectionId = 'mqtt_' . uniqid();
        $connectionInfo['connected_at'] = now();
        $connectionInfo['subscriptions'] = [];

        Cache::put($this->connectionId, $connectionInfo, now()->addSeconds(self::CONNECTION_TTL));
        Cache::put(self::CONNECTION_KEY, $this->connectionId, now()->addSeconds(self::CONNECTION_TTL));
    }

    public function connectIfNeeded(): void
    {
        // If already connected, do nothing
        if ($this->client !== null && $this->client->isConnected()) {
            Log::info('[MqttService] Already connected to MQTT broker.');
            return;
        }

        try {
            // Create connection settings with recommended defaults
            $settings = (new ConnectionSettings)
                ->setUsername($this->config['username'])
                ->setPassword($this->config['password'])
                ->setKeepAliveInterval(60)
                ->setLastWillTopic('vending/status')
                ->setLastWillMessage('client disconnected')
                ->setLastWillQualityOfService(1)
                ->setConnectTimeout((int)$this->config['connect_timeout'])
                ->setSocketTimeout((int)$this->config['socket_timeout']);

            // Generate a unique client ID if not set
            $clientId = $this->config['client_id'] ?? 'laravel_mqtt_client_' . uniqid();
            
            Log::info('[MqttService] Creating new MQTT client instance.', [
                'host' => $this->config['host'],
                'port' => (int)$this->config['port'],
                'clientId' => $clientId,
                'username' => $this->config['username'],
                'connect_timeout' => (int)$this->config['connect_timeout'],
                'socket_timeout' => (int)$this->config['socket_timeout']
            ]);
            
            $this->client = new MqttClient(
                $this->config['host'],
                (int)$this->config['port'],
                $clientId,
                MqttClient::MQTT_3_1_1
            );
            
            Log::info('[MqttService] Attempting to connect to MQTT broker...');
            
            // Connect with clean session
            $this->client->connect($settings, true);
            
            if ($this->client->isConnected()) {
                Log::info('[MqttService] Successfully connected to MQTT broker.');
                
                // Store connection info
                $this->storeConnection([
                    'host' => $this->config['host'],
                    'port' => (int)$this->config['port'],
                    'client_id' => $clientId,
                    'username' => $this->config['username'],
                    'password' => $this->config['password'],
                    'use_tls' => (bool)$this->config['use_tls'],
                    'clean_session' => true,
                    'connect_timeout' => (int)$this->config['connect_timeout'],
                    'keep_alive_interval' => (int)$this->config['keep_alive_interval'],
                    'socket_timeout' => (int)$this->config['socket_timeout']
                ]);
            } else {
                Log::error('[MqttService] Failed to connect to MQTT broker - client reports not connected.');
                throw new MqttClientException('Failed to establish connection - client reports not connected');
            }

        } catch (MqttClientException $e) {
            Log::error('[MqttService] MQTT Connection Exception during connectIfNeeded.', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        } catch (\Exception $e) {
            Log::error('[MqttService] Unexpected Exception during connectIfNeeded.', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    public function publish(string $topic, array $payload, int $qualityOfService = 0, bool $retain = false): void
    {
        try {
            // Attempt to connect only when publishing
            $this->connectIfNeeded();
            
            $jsonPayload = json_encode($payload);
            Log::info('[MqttService] Publishing to MQTT.', ['topic' => $topic, 'payload_preview' => substr($jsonPayload, 0, 200)]);
            
            $this->client->publish(
                $topic,
                $jsonPayload,
                $qualityOfService,
                $retain
            );
            Log::info('[MqttService] Successfully published to MQTT.', ['topic' => $topic]);

        } catch (MqttClientException $e) {
            Log::error('[MqttService] MQTT Publish Exception.', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            // Decide how to handle publish failure. Maybe throw custom exception.
            throw $e; 
        } catch (\Exception $e) {
            Log::error('[MqttService] Generic Exception during publish.', [
                'topic' => $topic,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    public function subscribe(string $topic, int $qualityOfService = 0): void
    {
        try {
            $this->connectIfNeeded();

            $this->client->subscribe($topic, function (string $topic, string $message) {
                Log::info("[MqttService] Message received", [
                    'topic' => $topic,
                    'message' => $message
                ]);
            }, $qualityOfService);

            // Store subscription
            if ($this->connectionId) {
                $connectionInfo = Cache::get($this->connectionId, []);
                if (!in_array($topic, $connectionInfo['subscriptions'] ?? [])) {
                    $connectionInfo['subscriptions'][] = $topic;
                    Cache::put($this->connectionId, $connectionInfo, now()->addSeconds(self::CONNECTION_TTL));
                }
            }

            Log::info('[MqttService] Successfully subscribed to topic.', ['topic' => $topic]);

        } catch (MqttClientException $e) {
            Log::error('[MqttService] MQTT Subscribe Exception.', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        }
    }

    public function unsubscribe(string $topic): void
    {
        try {
            if ($this->client && $this->client->isConnected()) {
                $this->client->unsubscribe($topic);

                // Remove from stored subscriptions
                if ($this->connectionId) {
                    $connectionInfo = Cache::get($this->connectionId, []);
                    if (isset($connectionInfo['subscriptions'])) {
                        $connectionInfo['subscriptions'] = array_diff($connectionInfo['subscriptions'], [$topic]);
                        Cache::put($this->connectionId, $connectionInfo, now()->addSeconds(self::CONNECTION_TTL));
                    }
                }

                Log::info('[MqttService] Successfully unsubscribed from topic.', ['topic' => $topic]);
            }
        } catch (MqttClientException $e) {
            Log::error('[MqttService] MQTT Unsubscribe Exception.', [
                'topic' => $topic,
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            throw $e;
        }
    }

    public function disconnect(): void
    {
        try {
            if ($this->client && $this->client->isConnected()) {
                // Get current subscriptions before disconnecting
                $subscriptions = [];
                if ($this->connectionId) {
                    $connectionInfo = Cache::get($this->connectionId, []);
                    $subscriptions = $connectionInfo['subscriptions'] ?? [];
                }

                // Unsubscribe from all topics
                foreach ($subscriptions as $topic) {
                    try {
                        $this->client->unsubscribe($topic);
                        Log::info('[MqttService] Unsubscribed from topic during disconnect.', ['topic' => $topic]);
                    } catch (\Exception $e) {
                        Log::warning('[MqttService] Failed to unsubscribe from topic during disconnect.', [
                            'topic' => $topic,
                            'error' => $e->getMessage()
                        ]);
                    }
                }

                $this->client->disconnect();
                
                // Clear connection from cache
                Cache::forget(self::CONNECTION_KEY);
                Cache::forget($this->connectionId);
                $this->connectionId = null;
                $this->client = null;

                Log::info('[MqttService] Successfully disconnected from MQTT broker.');
            }
        } catch (MqttClientException $e) {
            Log::error('[MqttService] Error during MQTT disconnect.', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function __destruct()
    {
        try {
            if ($this->client !== null && $this->client->isConnected()) {
                $this->disconnect();
            }
        } catch (MqttClientException $e) {
            Log::error('[MqttService] Error during MQTT disconnect in __destruct.', ['error' => $e->getMessage()]);
        }
    }

    public function checkConnectionStatus(): array
    {
        Log::debug('[MqttService] Effective config in checkConnectionStatus:', $this->config);

        try {
            $tempClientId = ($this->config['client_id'] ?? 'laravel_mqtt_client') . '_status_check_' . uniqid();
            
            // Use the same connection settings as the main connection
            $settings = (new ConnectionSettings)
                ->setUsername($this->config['username'])
                ->setPassword($this->config['password'])
                ->setKeepAliveInterval(60)
                ->setLastWillTopic('vending/status')
                ->setLastWillMessage('client disconnected')
                ->setLastWillQualityOfService(1)
                ->setConnectTimeout((int)$this->config['connect_timeout'])
                ->setSocketTimeout((int)$this->config['socket_timeout']);

            $tempClient = new MqttClient(
                $this->config['host'],
                (int)$this->config['port'],
                $tempClientId,
                MqttClient::MQTT_3_1_1
            );
            
            Log::info('[MqttService] Attempting status check connection to MQTT broker.', [
                'host' => $this->config['host'], 
                'port' => (int)$this->config['port'], 
                'clientId' => $tempClientId,
                'username' => $this->config['username']
            ]);

            $tempClient->connect($settings, true); // Use clean session
            
            if ($tempClient->isConnected()) {
                Log::info('[MqttService] Status check connection successful.');
                // $tempClient->disconnect();
                return ['status' => 'connected', 'message' => 'Successfully connected to MQTT broker.'];
            } else {
                Log::warning('[MqttService] Status check: connected() returned false after connect call.');
                return ['status' => 'disconnected', 'message' => 'Connection attempt made but status is not connected.'];
            }

        } catch (MqttClientException $e) {
            Log::warning('[MqttService] MQTT Connection Exception during status check.', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            return ['status' => 'disconnected', 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('[MqttService] Generic Exception during status check.', [
                'error' => $e->getMessage(),
            ]);
            return ['status' => 'disconnected', 'message' => 'A generic error occurred during MQTT status check.'];
        }
    }

    public function testManualConnection(array $params): array
    {
        $host = $params['host'] ?? 'localhost';
        $port = isset($params['port']) ? (int)$params['port'] : 1883;
        $username = $params['username'] ?? null;
        $password = $params['password'] ?? null;
        $useTls = isset($params['use_tls']) ? (bool)$params['use_tls'] : false;
        $clientId = 'phpTestClient123'; // Hardcoded client ID for testing

        // For manual test, we assume other TLS params like cafile etc., are not part of this simple form
        // If needed, they could be added to the $params array and form

        try {
            $settings = (new ConnectionSettings)
                ->setConnectTimeout($params['connect_timeout'] ?? 10)
                ->setKeepAliveInterval($params['keep_alive_interval'] ?? 10)
                ->setSocketTimeout($params['socket_timeout'] ?? 10);

            if ($useTls) {
                $settings->setUseTls(true);
                // Basic TLS, no CA/cert/key validation for this simple manual test form by default
                // For a more complete test, these would need to be passed and set:
                // ->setCaFile($params['tls_cafile'] ?? null)
                // ->setClientCertificateFile($params['tls_certfile'] ?? null)
                // ->setClientKeyFile($params['tls_keyfile'] ?? null)
                // ->setVerifyPeer($params['tls_verify_peer'] ?? true);
            } else {
                $settings->setUseTls(false);
            }

            if (!empty($username)) {
                $settings->setUsername($username);
            }
            if (!empty($password)) {
                $settings->setPassword($password);
            }

            $tempClient = new MqttClient(
                $host, 
                $port, 
                $clientId,
                MqttClient::MQTT_3_1_1
            );
            
            Log::info('[MqttService] Attempting manual test connection to MQTT broker.', [
                'host' => $host, 
                'port' => $port, 
                'clientId' => $clientId,
                'username' => $username, // Log username for manual test
                'use_tls' => $useTls
            ]);

            $tempClient->connect($settings, false); // Clean session
            
            if ($tempClient->isConnected()) {
                Log::info('[MqttService] Manual test connection successful.');
                $tempClient->disconnect();
                return ['status' => 'connected', 'message' => 'Successfully connected to MQTT broker with provided settings.'];
            } else {
                Log::warning('[MqttService] Manual test: connected() returned false.');
                return ['status' => 'disconnected', 'message' => 'Manual test: Connection attempt made but status is not connected.'];
            }

        } catch (MqttClientException $e) {
            Log::warning('[MqttService] MQTT Connection Exception during manual test.', [
                'error' => $e->getMessage(),
                'code' => $e->getCode(),
            ]);
            return ['status' => 'disconnected', 'message' => $e->getMessage()];
        } catch (\Exception $e) {
            Log::error('[MqttService] Generic Exception during manual test.', [
                'error' => $e->getMessage(),
            ]);
            return ['status' => 'disconnected', 'message' => 'A generic error occurred during manual MQTT test.'];
        }
    }
} 