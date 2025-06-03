<template>
    <Modal :show="show" @close="close">
        <div class="p-6">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                <slot name="title" />
            </h2>

            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                <slot name="content" />
            </p>

            <div class="mt-6 flex justify-end">
                <SecondaryButton @click="close">Cancel</SecondaryButton>

                <DangerButton
                    class="ml-3"
                    :class="{ 'opacity-25': processing }"
                    :disabled="processing"
                    @click="confirm"
                >
                    <slot name="footer" />
                </DangerButton>
            </div>
        </div>
    </Modal>
</template>

<script setup>
import { ref } from 'vue';
import Modal from '@/Components/Modal.vue';
import DangerButton from '@/Components/DangerButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close', 'confirmed']);

const processing = ref(false);

const close = () => {
    emit('close');
};

const confirm = () => {
    processing.value = true;

    emit('confirmed');
};
</script> 