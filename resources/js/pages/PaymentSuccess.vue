<template>
    <Head title="Payment Successful" />
    <div class="min-h-screen bg-gray-50 dark:bg-gray-900 py-12 px-4">
        <div class="max-w-md mx-auto">
            <div class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl p-8">
                <div class="text-center">
                    <!-- Success Animation -->
                    <div class="mb-6">
                        <div class="mx-auto w-16 h-16 bg-green-100 dark:bg-green-900 rounded-full flex items-center justify-center">
                            <svg class="h-10 w-10 text-green-500 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                        </div>
                    </div>
                    
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Payment Successful!</h2>
                    <div class="space-y-4">
                        <p class="text-gray-600 dark:text-gray-300">
                            Thank you for your purchase. Your transaction has been completed successfully.
                        </p>
                        <div class="bg-gray-50 dark:bg-gray-700/50 rounded-xl p-4">
                            <p class="text-sm text-gray-500 dark:text-gray-400">Transaction ID</p>
                            
                            <p class="text-lg font-medium text-gray-900 dark:text-white">{{ $page.url.split('=')[1] || order_id || '#123456789' }}</p>
                        </div>
                    </div>

                    <div class="mt-8 space-y-4">
                        <a href="/payment/vend/ABC123" 
                           class="block w-full bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white font-bold py-3 px-6 rounded-xl transition-all duration-200">
                            Return to Home
                        </a>
                        <button @click="downloadReceipt" 
                                class="block w-full bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-300 font-medium py-3 px-6 rounded-xl transition-all duration-200">
                            Download Receipt
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

const downloadReceipt = () => {
    // Implement receipt download functionality
    console.log('Downloading receipt...');
};
</script> 