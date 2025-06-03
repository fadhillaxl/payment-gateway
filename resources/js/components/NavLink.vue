<template>
    <Link :href="href" :class="{ 'bg-gray-900 text-white': active, 'text-gray-300 hover:bg-gray-700 hover:text-white': ! active }" class="rounded-md px-3 py-2 text-sm font-medium" aria-current="page">
        <slot />
    </Link>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    href: {
        type: String,
        required: true,
    },
    active: {
        type: Boolean,
    },
});
</script> 