<template>
    <Head title="Payment Processing" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4">
        <div class="max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <div class="text-center">
                    <!-- Status Indicator -->
                    <div class="mb-6">
                        <div class="mx-auto w-16 h-16 bg-blue-100 dark:bg-blue-900 rounded-full flex items-center justify-center">
                            <div :class="statusClass" class="text-blue-500 dark:text-blue-400">
                                <div class="animate-pulse w-8 h-8 border-4 border-current rounded-full border-r-transparent"></div>
                            </div>
                        </div>
                    </div>

                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Processing Your Order</h2>
                    
                    <!-- Connection Status -->
                    <div class="mb-6">
                        <p :class="[
                            'inline-flex items-center px-4 py-2 rounded-full text-sm font-medium',
                            {
                                'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200': statusClass.includes('connected'),
                                'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200': statusClass.includes('connecting'),
                                'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200': statusClass.includes('disconnected')
                            }
                        ]">
                            <span class="w-2 h-2 mr-2 rounded-full" :class="[
                                'bg-current',
                                { 'animate-pulse': statusClass.includes('connecting') }
                            ]"></span>
                            {{ statusText }}
                        </p>
                    </div>

                    <!-- Device Controls -->
                    <div class="space-y-4">
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6">
                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-4">Device Control</h3>
                            <div class="flex space-x-4">
                                <button 
                                    @click="turnOn"
                                    class="flex-1 bg-green-500 hover:bg-green-600 text-white font-medium py-3 px-6 rounded-xl transition-all duration-200">
                                    Turn On
                                </button>
                                <button 
                                    @click="turnOff"
                                    class="flex-1 bg-red-500 hover:bg-red-600 text-white font-medium py-3 px-6 rounded-xl transition-all duration-200">
                                    Turn Off
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-8">
                        <a href="/payment" 
                           class="block w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200">
                            Return to Home
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';
import { Client, Message } from 'paho-mqtt';

const broker: string = '5e0dd55a391f437aa542e089127bdc2b.s1.eu.hivemq.cloud';
const port: number = 8884;
const clientId: string = 'webClient-' + Math.random().toString(16).substr(2, 8);
const username: string = 'admin';
const password: string = 'Admin123';
const topic: string = 'led/control';

const statusText = ref('Connecting...');
const statusClass = ref('status connecting');

let client: Client;

const connect = () => {
    client = new Client(broker, port, clientId);

    client.onConnectionLost = onConnectionLost;
    client.onMessageArrived = onMessageArrived;

    client.connect({
        useSSL: true,
        userName: username,
        password: password,
        onSuccess: onConnect,
        onFailure: onFailure
    });
};

const onConnect = () => {
    console.log('Connected to MQTT broker');
    statusText.value = 'Connected';
    statusClass.value = 'status connected';
    const id_data = localStorage.getItem('selectedItemId');
    console.log('id_data:', id_data);

    if (id_data === '1') {
        console.log('id 1 selected');
        sendMessage('on');
    } else if (id_data === '2') {
        console.log('id 2 selected');
        // sendMessage('on');
    } else if (id_data === '3') {
        console.log('id 3 selected');
        // sendMessage('on');
    }
};

const onFailure = (message: { errorMessage: string }) => {
    console.log('Connection failed:', message.errorMessage);
    statusText.value = 'Connection failed';
    statusClass.value = 'status disconnected';
};

const onConnectionLost = (responseObject: { errorCode: number; errorMessage: string }) => {
    if (responseObject.errorCode !== 0) {
        console.log('Connection lost:', responseObject.errorMessage);
        statusText.value = 'Connection lost';
        statusClass.value = 'status disconnected';
    }
};

const onMessageArrived = (message: Message) => {
    console.log('Message arrived:', message.payloadString);
};

const sendMessage = (payload: string) => {
    if (client && client.isConnected()) {
        const message = new Message(payload);
        message.destinationName = topic;
        client.send(message);
    } else {
        console.log('Client not connected');
    }
};

const turnOn = () => sendMessage('on');
const turnOff = () => sendMessage('off');

onMounted(() => {
    connect();
});
</script>

<style scoped>
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}

@keyframes pulse {
    0%, 100% {
        opacity: 1;
    }
    50% {
        opacity: .5;
    }
}
</style>
