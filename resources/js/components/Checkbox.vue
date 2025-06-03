<template>
    <input
        type="checkbox"
        :checked="checked"
        @change="$emit('update:checked', $event.target.checked)"
        class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-indigo-600 shadow-sm focus:ring-indigo-500 dark:focus:ring-indigo-600 dark:focus:ring-offset-gray-800"
    >
</template>

<script setup>
defineProps({
    checked: {
        type: [Array, Boolean],
        default: false,
    },
});

defineEmits(['update:checked']);
</script> 