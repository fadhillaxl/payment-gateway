<template>
    <Head title="Payment" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12">
        <div class="container mx-auto px-4">
            <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <div class="flex items-center justify-between mb-8">
                    <h1 class="text-3xl font-bold text-gray-900 dark:text-white">Smart Vending Machine</h1>
                    <div class="bg-blue-100 dark:bg-blue-900 rounded-lg px-4 py-2">
                        <span class="text-blue-800 dark:text-blue-200 font-medium">Status: Ready</span>
                    </div>
                </div>

                <!-- Vending Machine Grid -->
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
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

                <!-- Selected Items Summary -->
                <div class="mt-12" v-if="selectedItems.length > 0">
                    <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-6">
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

                <div class="mt-8">
                    <button
                        @click.prevent="handlePayment"
                        class="w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-4 px-6 rounded-xl transition-all duration-200 flex items-center justify-center space-x-2"
                        :disabled="!isValidAmount || loading"
                        :class="{ 'opacity-50 cursor-not-allowed': !isValidAmount || loading }">
                        <span v-if="loading" class="animate-spin rounded-full h-5 w-5 border-b-2 border-white"></span>
                        <span>{{ loading ? 'Processing Payment...' : 'Pay with Midtrans' }}</span>
                    </button>
                    <p v-if="amountError" class="mt-2 text-red-500 text-center">{{ amountError }}</p>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, onUnmounted, computed } from 'vue';
import axios from 'axios';

const props = defineProps<{
    clientKey: string;
}>();

// Menu items
const menuItems = ref([
    { id: 1, name: 'Menu 1 - Alat A', price: 500, image: 'https://d2v5dzhdg4zhx3.cloudfront.net/web-assets/images/storypages/primary/ProductShowcasesampleimages/JPEG/Product+Showcase-1.jpg' },
    { id: 2, name: 'Menu 2 - Alat B', price: 1000, image: 'https://d2v5dzhdg4zhx3.cloudfront.net/web-assets/images/storypages/primary/ProductShowcasesampleimages/JPEG/Product+Showcase-1.jpg' },
    { id: 3, name: 'Menu 3 - Alat C', price: 2000, image: 'https://d2v5dzhdg4zhx3.cloudfront.net/web-assets/images/storypages/primary/ProductShowcasesampleimages/JPEG/Product+Showcase-1.jpg' },
]);

const selectedItemIds = ref<number[]>([]);
const customAmount = ref<number>(0);
const loading = ref<boolean>(false);
const snapToken = ref<string>('');
const amountError = ref<string>('');

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
    return selectedItems.value.length > 0 && !amountError.value;
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

const getSnapToken = async () => {
    try {
        if (!isValidAmount.value) {
            throw new Error('Invalid amount');
        }

        loading.value = true;
        const response = await axios.post('/payment/token', {
            amount: totalPrice.value,
            items: selectedItems.value.map(item => ({
                id: item.id,
                name: item.name,
                price: item.price,
                quantity: getItemCount(item.id)
            })),
        });
        snapToken.value = response.data.snapToken;
        return snapToken.value;
    } catch (error) {
        console.error('Error getting snap token:', error);
        throw error;
    } finally {
        loading.value = false;
    }
};

const handlePayment = async () => {
    try {
        const snapToken = await getSnapToken();
        
        // @ts-ignore
        window.snap.pay(snapToken, {
            onSuccess: function(result: any) {
                console.log('success', result);
                window.location.href = `/payment/success?amount=${totalPrice.value}`;
            },
            onPending: function(result: any) {
                console.log('pending', result);
                window.location.href = `/payment/pending?amount=${totalPrice.value}`;
            },
            onError: function(result: any) {
                console.log('error', result);
                window.location.href = `/payment/error?amount=${totalPrice.value}`;
            },
            onClose: function() {
                console.log('customer closed the popup without finishing the payment');
                window.location.href = '/payment/error';
            }
        });
    } catch (error: any) {
        console.error('Payment error:', error);
        amountError.value = error.response?.data?.error || error.message || 'An error occurred while processing your payment';
    }
};

// Load Midtrans script
const loadMidtransScript = () => {
    const script = document.createElement('script');
    script.src = 'https://app.sandbox.midtrans.com/snap/snap.js';
    script.setAttribute('data-client-key', props.clientKey);
    script.async = true;
    document.body.appendChild(script);
};

// Load script when component is mounted
onMounted(() => {
    loadMidtransScript();
});

// Clean up script when component is unmounted
onUnmounted(() => {
    const script = document.querySelector('script[src*="midtrans.com/snap/snap.js"]');
    if (script) {
        document.body.removeChild(script);
    }
});

const getItemCount = (itemId: number) => {
    return selectedItemIds.value.filter(id => id === itemId).length;
};
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
</style>