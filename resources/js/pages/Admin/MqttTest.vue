<template>
    <Head title="MQTT Connection Test" />
    <div class="min-h-screen bg-gray-100 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
        <div class="max-w-4xl mx-auto">
            <!-- Connection Form -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8 mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-6">MQTT Connection Tester</h1>

                <form @submit.prevent="submitTest">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="host" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Host</label>
                            <input type="text" v-model="form.host" id="host" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <div>
                            <label for="port" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Port</label>
                            <input type="number" v-model.number="form.port" id="port" required
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username (optional)</label>
                            <input type="text" v-model="form.username" id="username"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password (optional)</label>
                            <input type="password" v-model="form.password" id="password"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <div>
                            <label for="client_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Client ID (optional)</label>
                            <input type="text" v-model="form.client_id" id="client_id"
                                   class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                        </div>

                        <div class="flex items-center space-x-4">
                            <div class="flex items-center">
                                <input id="use_tls" v-model="form.use_tls" type="checkbox"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:focus:ring-offset-gray-800">
                                <label for="use_tls" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Use TLS/SSL</label>
                            </div>
                            <div class="flex items-center">
                                <input id="clean_session" v-model="form.clean_session" type="checkbox"
                                       class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:focus:ring-offset-gray-800">
                                <label for="clean_session" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Clean Session</label>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-4">
                        <button type="button" @click="disconnect"
                                :disabled="!isConnected || loading"
                                class="px-4 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-300 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                            Disconnect
                        </button>
                        <button type="submit"
                                :disabled="loading"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <span v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span>
                            {{ loading ? 'Testing...' : 'Test Connection' }}
                        </button>
                    </div>
                </form>

                <div v-if="connectionResult" class="mt-6 p-4 rounded-md"
                     :class="{
                         'bg-green-50 dark:bg-green-700/30 text-green-700 dark:text-green-300': connectionResult.status === 'connected',
                         'bg-red-50 dark:bg-red-700/30 text-red-700 dark:text-red-300': connectionResult.status === 'disconnected' || connectionResult.status === 'validation_error',
                         'bg-yellow-50 dark:bg-yellow-700/30 text-yellow-700 dark:text-yellow-300': connectionResult.status === 'error'
                     }">
                    <h3 class="text-lg font-medium mb-2">Connection Status: <span class="capitalize">{{ connectionResult.status.replace('_',' ') }}</span></h3>
                    <p class="text-sm">{{ connectionResult.message }}</p>
                    <div v-if="connectionResult.errors" class="mt-2 text-sm">
                        <p>Details:</p>
                        <ul class="list-disc list-inside">
                            <li v-for="(errorMessages, field) in connectionResult.errors" :key="field">
                                {{ field }}: {{ errorMessages.join(', ') }}
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Publish Form -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8 mb-6">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Publish Message</h2>
                <form @submit.prevent="publishMessage" class="space-y-4">
                    <div>
                        <label for="publish_topic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Topic</label>
                        <input type="text" v-model="publishForm.topic" id="publish_topic" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                    </div>

                    <div>
                        <label for="publish_message" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Message</label>
                        <textarea v-model="publishForm.message" id="publish_message" rows="3" required
                                  class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200"></textarea>
                    </div>

                    <div class="flex items-center space-x-4">
                        <div>
                            <label for="publish_qos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">QoS</label>
                            <select v-model.number="publishForm.qos" id="publish_qos"
                                    class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                                <option :value="0">0 - At most once</option>
                                <option :value="1">1 - At least once</option>
                                <option :value="2">2 - Exactly once</option>
                            </select>
                        </div>

                        <div class="flex items-center mt-6">
                            <input id="publish_retain" v-model="publishForm.retain" type="checkbox"
                                   class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-gray-300 dark:border-gray-600 rounded dark:bg-gray-700 dark:focus:ring-offset-gray-800">
                            <label for="publish_retain" class="ml-2 block text-sm text-gray-900 dark:text-gray-300">Retain</label>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="submit"
                                :disabled="!isConnected || publishLoading"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <span v-if="publishLoading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span>
                            {{ publishLoading ? 'Publishing...' : 'Publish' }}
                        </button>
                    </div>
                </form>

                <div v-if="publishResult" class="mt-4 p-4 rounded-md"
                     :class="{
                         'bg-green-50 dark:bg-green-700/30 text-green-700 dark:text-green-300': publishResult.status === 'success',
                         'bg-red-50 dark:bg-red-700/30 text-red-700 dark:text-red-300': publishResult.status === 'error'
                     }">
                    <p class="text-sm">{{ publishResult.message }}</p>
                </div>
            </div>

            <!-- Subscribe Form -->
            <div class="bg-white dark:bg-gray-800 shadow-xl rounded-lg p-6 md:p-8">
                <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-4">Subscribe to Topic</h2>
                <form @submit.prevent="subscribeTopic" class="space-y-4">
                    <div>
                        <label for="subscribe_topic" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Topic</label>
                        <input type="text" v-model="subscribeForm.topic" id="subscribe_topic" required
                               class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                    </div>

                    <div>
                        <label for="subscribe_qos" class="block text-sm font-medium text-gray-700 dark:text-gray-300">QoS</label>
                        <select v-model.number="subscribeForm.qos" id="subscribe_qos"
                                class="mt-1 block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm dark:bg-gray-700 dark:text-gray-200">
                            <option :value="0">0 - At most once</option>
                            <option :value="1">1 - At least once</option>
                            <option :value="2">2 - Exactly once</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-4">
                        <button type="button" @click="unsubscribeTopic"
                                :disabled="!isConnected || !subscribeForm.topic || subscribeLoading"
                                class="px-4 py-2 border border-red-300 dark:border-red-600 rounded-md shadow-sm text-sm font-medium text-red-700 dark:text-red-300 bg-white dark:bg-gray-800 hover:bg-red-50 dark:hover:bg-red-900/20 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500 disabled:opacity-50">
                            Unsubscribe
                        </button>
                        <button type="submit"
                                :disabled="!isConnected || subscribeLoading"
                                class="px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50">
                            <span v-if="subscribeLoading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white mr-2"></span>
                            {{ subscribeLoading ? 'Subscribing...' : 'Subscribe' }}
                        </button>
                    </div>
                </form>

                <div v-if="subscribeResult" class="mt-4 p-4 rounded-md"
                     :class="{
                         'bg-green-50 dark:bg-green-700/30 text-green-700 dark:text-green-300': subscribeResult.status === 'success',
                         'bg-red-50 dark:bg-red-700/30 text-red-700 dark:text-red-300': subscribeResult.status === 'error'
                     }">
                    <p class="text-sm">{{ subscribeResult.message }}</p>
                </div>

                <!-- Received Messages -->
                <div v-if="receivedMessages.length > 0" class="mt-6">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-2">Received Messages</h3>
                    <div class="space-y-2">
                        <div v-for="(message, index) in receivedMessages" :key="index"
                             class="p-3 bg-gray-50 dark:bg-gray-700/50 rounded-lg">
                            <div class="text-sm font-medium text-gray-900 dark:text-white">Topic: {{ message.topic }}</div>
                            <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ message.message }}</div>
                            <div class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ message.timestamp }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import axios from 'axios';

// Connection form
const form = ref({
    host: '103.116.203.74',
    port: 1883,
    username: 'vending',
    password: 'vending61',
    use_tls: false,
    client_id: '',
    clean_session: true,
});

// Publish form
const publishForm = ref({
    topic: '',
    message: '',
    qos: 0,
    retain: false,
});

// Subscribe form
const subscribeForm = ref({
    topic: '',
    qos: 0,
});

// Loading states
const loading = ref(false);
const publishLoading = ref(false);
const subscribeLoading = ref(false);

// Results
const connectionResult = ref<any>(null);
const publishResult = ref<any>(null);
const subscribeResult = ref<any>(null);

// Received messages
const receivedMessages = ref<Array<{topic: string; message: string; timestamp: string}>>([]);

// Connection status
const isConnected = computed(() => connectionResult.value?.status === 'connected');

// Status check interval
let statusCheckInterval: number | null = null;

// Methods
const checkStatus = async () => {
    try {
        const response = await axios.get('/admin/mqtt-test/status');
        connectionResult.value = response.data;
    } catch (error) {
        console.error('Error checking MQTT status:', error);
    }
};

const submitTest = async () => {
    loading.value = true;
    connectionResult.value = null;
    try {
        const response = await axios.post('/admin/mqtt-test/submit', form.value);
        connectionResult.value = response.data;
    } catch (error: any) {
        if (error.response?.data) {
            connectionResult.value = error.response.data;
        } else {
            connectionResult.value = {
                status: 'error',
                message: error.message || 'An unknown error occurred while testing the connection.',
            };
        }
        console.error('Error testing MQTT connection:', error);
    }
    loading.value = false;
};

const disconnect = async () => {
    try {
        const response = await axios.post('/admin/mqtt-test/disconnect');
        connectionResult.value = response.data;
        receivedMessages.value = [];
    } catch (error: any) {
        console.error('Error disconnecting from MQTT:', error);
        connectionResult.value = {
            status: 'error',
            message: error.response?.data?.message || 'Failed to disconnect from MQTT broker.',
        };
    }
};

const publishMessage = async () => {
    publishLoading.value = true;
    publishResult.value = null;
    try {
        const response = await axios.post('/admin/mqtt-test/publish', publishForm.value);
        publishResult.value = response.data;
        publishForm.value.message = ''; // Clear message after successful publish
    } catch (error: any) {
        publishResult.value = {
            status: 'error',
            message: error.response?.data?.message || 'Failed to publish message.',
        };
        console.error('Error publishing MQTT message:', error);
    }
    publishLoading.value = false;
};

const subscribeTopic = async () => {
    subscribeLoading.value = true;
    subscribeResult.value = null;
    try {
        const response = await axios.post('/admin/mqtt-test/subscribe', subscribeForm.value);
        subscribeResult.value = response.data;
    } catch (error: any) {
        subscribeResult.value = {
            status: 'error',
            message: error.response?.data?.message || 'Failed to subscribe to topic.',
        };
        console.error('Error subscribing to MQTT topic:', error);
    }
    subscribeLoading.value = false;
};

const unsubscribeTopic = async () => {
    subscribeLoading.value = true;
    subscribeResult.value = null;
    try {
        const response = await axios.post('/admin/mqtt-test/unsubscribe', { topic: subscribeForm.value.topic });
        subscribeResult.value = response.data;
        subscribeForm.value.topic = ''; // Clear topic after successful unsubscribe
    } catch (error: any) {
        subscribeResult.value = {
            status: 'error',
            message: error.response?.data?.message || 'Failed to unsubscribe from topic.',
        };
        console.error('Error unsubscribing from MQTT topic:', error);
    }
    subscribeLoading.value = false;
};

// Lifecycle hooks
onMounted(() => {
    checkStatus();
    statusCheckInterval = window.setInterval(checkStatus, 5000); // Check status every 5 seconds
});

onUnmounted(() => {
    if (statusCheckInterval) {
        clearInterval(statusCheckInterval);
    }
});
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
</style> 