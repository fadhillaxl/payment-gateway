<template>
    <Head :title="`${machine.name} - Order`" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <!-- Header Section -->
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">{{ machine.name }}</h1>
                    <!-- MQTT Status Indicator -->
                    <div 
                        class="flex items-center space-x-2 px-3 py-1.5 rounded-lg text-sm font-medium"
                        :class="{
                            'bg-yellow-100 dark:bg-yellow-700 text-yellow-700 dark:text-yellow-200': mqttStatus === 'checking',
                            'bg-green-100 dark:bg-green-700 text-green-700 dark:text-green-200': mqttStatus === 'connected',
                            'bg-red-100 dark:bg-red-700 text-red-700 dark:text-red-200': mqttStatus === 'disconnected'
                        }"
                        :title="mqttStatusMessage"
                    >
                        <span class="w-2 h-2 rounded-full" :class="{
                            'bg-yellow-500': mqttStatus === 'checking',
                            'bg-green-500': mqttStatus === 'connected',
                            'bg-red-500': mqttStatus === 'disconnected'
                        }"></span>
                        <span>{{ mqttStatusMessage }}</span>
                    </div>
                </div>
                <div class="text-sm text-gray-600 dark:text-gray-400 mb-8">Location: {{ machine.location }}</div>

                <!-- Two Column Layout -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- Left Column: Product Selection -->
                    <div>
                        <!-- Vending Machine Grid -->
                        <div class="grid grid-cols-2 gap-4">
                            <div v-for="item in menuItems" :key="item.id" 
                                 class="bg-white dark:bg-gray-700 rounded-xl shadow-md hover:shadow-lg transition-all duration-300 overflow-hidden">
                                <div class="relative">
                                    <img :src="item.image" alt="Item Image" class="w-full h-32 object-cover" />
                                    <div class="absolute top-2 right-2 bg-white dark:bg-gray-800 rounded-full px-2 py-1 text-sm">
                                        <span class="text-gray-600 dark:text-gray-300">x{{ getItemCount(item.id) }}</span>
                                    </div>
                                </div>
                                <div class="p-4">
                                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">{{ item.name }}</h3>
                                    <p class="text-blue-600 dark:text-blue-400 font-medium my-2">IDR {{ formatPrice(item.price) }}</p>
                                    <button 
                                        @click="toggleItem(item, getItemCount(item.id) > 0 ? 'remove' : 'add')"
                                        :class="getItemCount(item.id) > 0 ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'"
                                        class="w-full text-white font-medium py-2 px-4 rounded-lg transition-colors duration-200">
                                        {{ getItemCount(item.id) > 0 ? 'Remove' : 'Add to Cart' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Cart Summary -->
                        <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6" v-if="selectedItems.length > 0">
                            <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Your Cart</h2>
                            <ul class="space-y-4">
                                <li v-for="item in selectedItems" :key="item.id" 
                                    class="flex justify-between items-center bg-white dark:bg-gray-700 p-4 rounded-lg shadow-sm">
                                    <div class="flex items-center space-x-4">
                                        <span class="text-gray-700 dark:text-gray-300 font-medium">{{ item.name }}</span>
                                        <div class="flex items-center space-x-2 bg-gray-100 dark:bg-gray-600 rounded-lg p-1">
                                            <button 
                                                @click="toggleItem(item, 'remove')"
                                                class="text-red-500 hover:text-red-600 w-8 h-8 flex items-center justify-center rounded-md transition-colors">
                                                <span class="text-lg">-</span>
                                            </button>
                                            <span class="text-gray-700 dark:text-gray-300 w-8 text-center">{{ getItemCount(item.id) }}</span>
                                            <button 
                                                @click="toggleItem(item, 'add')"
                                                class="text-blue-500 hover:text-blue-600 w-8 h-8 flex items-center justify-center rounded-md transition-colors">
                                                <span class="text-lg">+</span>
                                            </button>
                                        </div>
                                    </div>
                                    <span class="text-gray-600 dark:text-gray-400 font-medium">IDR {{ formatPrice(item.price * getItemCount(item.id)) }}</span>
                                </li>
                            </ul>
                            <div class="mt-6 flex justify-between items-center border-t dark:border-gray-600 pt-4">
                                <span class="text-gray-600 dark:text-gray-400">Total Amount</span>
                                <span class="text-2xl font-bold text-gray-900 dark:text-white">IDR {{ formatPrice(totalPrice) }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Payment Form -->
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6">
                        <h2 class="text-xl font-semibold text-gray-800 dark:text-gray-200 mb-4">Payment</h2>
                        
                        <!-- Snap Payment Form -->
                        <div v-if="snapToken" id="snap-container" class="w-full"></div>
                        
                        <!-- Payment Button -->
                        <button
                            v-else
                            @click.prevent="handlePayment"
                            class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2"
                            :disabled="!isValidAmount || loading"
                            :class="{ 'opacity-50 cursor-not-allowed': !isValidAmount || loading }">
                            <span v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                            <span>{{ loading ? 'Processing...' : 'Proceed to Payment' }}</span>
                        </button>
                        
                        <p v-if="error" class="mt-2 text-red-500 text-center">{{ error }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted, watch } from 'vue';
import axios from 'axios';

const props = defineProps<{
    machine: {
        id: number;
        name: string;
        token: string;
        location: string;
        topic: string;
    };
    clientKey: string;
}>();

// Menu items
const menuItems = ref([
    { id: 1, name: 'Cola', price: 5000, image: 'https://d2v5dzhdg4zhx3.cloudfront.net/web-assets/images/storypages/primary/ProductShowcasesampleimages/JPEG/Product+Showcase-1.jpg' },
    { id: 2, name: 'Sprite', price: 5000, image: 'https://d2v5dzhdg4zhx3.cloudfront.net/web-assets/images/storypages/primary/ProductShowcasesampleimages/JPEG/Product+Showcase-1.jpg' },
    { id: 3, name: 'Fanta', price: 5000, image: 'https://d2v5dzhdg4zhx3.cloudfront.net/web-assets/images/storypages/primary/ProductShowcasesampleimages/JPEG/Product+Showcase-1.jpg' },
    { id: 4, name: 'Water', price: 3000, image: 'https://d2v5dzhdg4zhx3.cloudfront.net/web-assets/images/storypages/primary/ProductShowcasesampleimages/JPEG/Product+Showcase-1.jpg' },
]);

const selectedItemIds = ref<number[]>([]);
const loading = ref<boolean>(false);
const error = ref<string>('');
const snapToken = ref<string>('');
const snapInstance = ref<any>(null);
const mqttStatus = ref('checking'); // checking, connected, disconnected
const mqttStatusMessage = ref('Checking MQTT connection status...');

const selectedItems = computed(() => {
    const items = menuItems.value.filter(item => selectedItemIds.value.includes(item.id));
    return items.map(item => ({
        ...item,
        quantity: getItemCount(item.id)
    }));
});

const totalPrice = computed(() => {
    return selectedItems.value.reduce((sum, item) => {
        return sum + (item.price * getItemCount(item.id));
    }, 0);
});

const isValidAmount = computed(() => {
    return selectedItems.value.length > 0 && !error.value;
});

const formatPrice = (price: number) => {
    return new Intl.NumberFormat('id-ID').format(price);
};

const toggleItem = (item: any, action: 'add' | 'remove') => {
    if (action === 'add') {
        selectedItemIds.value.push(item.id);
    } else if (action === 'remove') {
        const index = selectedItemIds.value.lastIndexOf(item.id);
        if (index !== -1) {
            selectedItemIds.value.splice(index, 1);
        }
    }
};

const getItemCount = (itemId: number) => {
    return selectedItemIds.value.filter(id => id === itemId).length;
};

const fetchMqttStatus = async () => {
    mqttStatus.value = 'checking';
    mqttStatusMessage.value = 'Checking MQTT connection status...';
    try {
        const response = await axios.get('/payment/vend/mqtt-status');
        mqttStatus.value = response.data.status;
        mqttStatusMessage.value = response.data.message;
        if (response.data.status === 'connected') {
            // You could potentially trigger something here if needed upon successful connection check
        }
    } catch (err) {
        console.error('Error fetching MQTT status:', err);
        mqttStatus.value = 'disconnected';
        mqttStatusMessage.value = 'Failed to fetch MQTT status. Please check backend logs.';
    }
};

const handlePayment = async () => {
    try {
        loading.value = true;
        error.value = '';

        const response = await axios.post('/payment/vend/order', {
            token: props.machine.token,
            items: selectedItems.value.map(item => ({
                name: item.name,
                quantity: getItemCount(item.id),
                price: item.price
            })),
            amount: totalPrice.value
        });

        snapToken.value = response.data.snap_token;

    } catch (err: any) {
        console.error('Order error:', err);
        error.value = err.response?.data?.message || err.message || 'An error occurred while processing your order';
        loading.value = false;
    }
};

// Watch for snapToken changes to initialize Snap
watch(snapToken, (newToken) => {
    if (newToken && window.snap) {
        // @ts-ignore
        snapInstance.value = window.snap.pay(newToken, {
            onSuccess: async function(result) {
                console.log('Payment success:', result);
                
                try {
                    // Update transaction status
                    await axios.post('/payment/update-status', {
                        order_id: result.order_id,
                        transaction_status: result.transaction_status,
                        payment_type: result.payment_type
                    });
                    
                    // Redirect to success page
                    window.location.href = `/payment/success?order_id=${result.order_id}`;
                } catch (error) {
                    console.error('Failed to update transaction status:', error);
                    // Still redirect to success page even if update fails
                    window.location.href = `/payment/success?order_id=${result.order_id}`;
                }
            },
            onPending: function(result) {
                console.log('Payment pending:', result);
                window.location.href = `/payment/pending?order_id=${result.order_id}`;
            },
            onError: function(result) {
                console.log('Payment error:', result);
                window.location.href = `/payment/error?order_id=${result.order_id}`;
            },
            onClose: function() {
                console.log('Customer closed the payment form');
                snapToken.value = '';
                error.value = 'Payment was cancelled';
                loading.value = false;
            }
        });
    }
});

// Load Midtrans script
const loadMidtransScript = () => {
    const script = document.createElement('script');
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', props.clientKey);
    script.async = true;
    document.body.appendChild(script);
};

onMounted(() => {
    // Check MQTT status immediately
    fetchMqttStatus();
    
    // Set up interval to check MQTT status every 5 seconds
    const statusInterval = setInterval(fetchMqttStatus, 5000);
    
    // Clean up interval on component unmount
    onUnmounted(() => {
        clearInterval(statusInterval);
    });
    
    loadMidtransScript();
});

onUnmounted(() => {
    const script = document.querySelector('script[src*="midtrans.com/snap/snap.js"]');
    if (script) {
        document.body.removeChild(script);
    }
    if (snapInstance.value) {
        snapInstance.value.destroy();
    }
});
</script>

<style scoped>
.animate-spin {
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from {
        transform: rotate(0deg);
    }
    to {
        transform: rotate(360deg);
    }
}

/* Snap Container Styles */
#snap-container {
    min-height: 400px;
    background: white;
    border-radius: 0.75rem;
    overflow: hidden;
}

/* Dark mode support for Snap container */
.dark #snap-container {
    background: #1f2937;
}
</style> 