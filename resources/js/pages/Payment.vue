<template>
    <Head title="Payment" />
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-4xl mx-auto bg-white dark:bg-gray-800 rounded-lg shadow-md p-6">
            <h1 class="text-2xl font-bold mb-4 text-gray-900 dark:text-white">Vending Machine</h1>

            <!-- Vending Machine Grid -->
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                <div v-for="item in menuItems" :key="item.id" 
                     class="border rounded-lg p-4 flex flex-col items-center justify-between bg-gray-50 dark:bg-gray-700">
                    <img :src="item.image" alt="Item Image" class="w-16 h-16 object-cover mb-2 rounded" />
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-2">{{ item.name }}</h3>
                    <p class="text-gray-600 dark:text-gray-400 mb-4">IDR {{ formatPrice(item.price) }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">x{{ getItemCount(item.id) }}</p>
                    <button 
                        @click="toggleItem(item, getItemCount(item.id) > 0 ? 'remove' : 'add')"
                        :class="getItemCount(item.id) > 0 ? 'bg-red-500 hover:bg-red-600' : 'bg-blue-500 hover:bg-blue-600'"
                        class="text-white font-bold py-1 px-3 rounded">
                        {{ getItemCount(item.id) > 0 ? 'Remove' : 'Add' }}
                    </button>
                </div>
            </div>

            <!-- Selected Items Summary -->
            <div class="mt-8" v-if="selectedItems.length > 0">
                <h2 class="text-lg font-semibold mb-3 text-gray-800 dark:text-gray-200">Selected Items</h2>
                <ul class="space-y-2">
                    <li v-for="item in selectedItems" :key="item.id" class="flex justify-between items-center">
                        <div class="flex items-center space-x-2">
                            <span class="text-gray-700 dark:text-gray-300">{{ item.name }} x{{ getItemCount(item.id) }}</span>
                            <button 
                                @click="toggleItem(item, 'add')"
                                class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-1 px-2 rounded">
                                +
                            </button>
                            <button 
                                @click="toggleItem(item, 'remove')"
                                class="bg-red-500 hover:bg-red-600 text-white font-bold py-1 px-2 rounded">
                                -
                            </button>
                        </div>
                        <span class="text-gray-600 dark:text-gray-400">IDR {{ formatPrice(item.price * getItemCount(item.id)) }}</span>
                    </li>
                </ul>
                <div class="mt-4 font-bold text-gray-900 dark:text-white">
                    Total: IDR {{ formatPrice(totalPrice) }}
                </div>
            </div>

            <button
                @click="handlePayment"
                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mt-4"
                :disabled="!isValidAmount || loading">
                {{ loading ? 'Processing...' : 'Bayar dengan Midtrans' }}
            </button>
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
    return menuItems.value.filter(item => selectedItemIds.value.includes(item.id));
});

const totalPrice = computed(() => {
    return selectedItems.value.reduce((sum, item) => sum + item.price, 0);
});

const isValidAmount = computed(() => {
    return totalPrice.value >= 10000 && !amountError.value;
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
                window.location.href = '/payment/success';
            },
            onPending: function(result: any) {
                console.log('pending', result);
                window.location.href = '/payment/pending';
            },
            onError: function(result: any) {
                console.log('error', result);
                window.location.href = '/payment/error';
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