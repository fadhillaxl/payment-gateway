<template>
    <div class="relative">
        <input
            :type="type"
            :name="name"
            :value="modelValue"
            :placeholder="placeholder"
            :class="classes"
            :disabled="disabled"
            :readonly="readonly"
            :autocomplete="autocomplete"    
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur')"
            @focus="$emit('focus')"
            @change="$emit('change')"
            @keydown="$emit('keydown')"
            @keyup="$emit('keyup')"
            @keypress="$emit('keypress')"
        >
        <div v-if="label" class="block text-sm font-medium text-gray-700">
            {{ label }}
        </div>
    </div>
</template>

<script setup>
defineProps({
    type: {
        type: String,
        default: 'text',
    },
    name: {
        type: String,
        required: true,
    },
    modelValue: {
        type: [String, Number, Boolean, Array, Object],
        default: '',
    },
    placeholder: {
        type: String,
        default: '',
    },
    classes: {
        type: String,
        default: '',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    readonly: {
        type: Boolean,
        default: false,
    },
    autocomplete: {
        type: String,
        default: 'off',
    },
    label: {
        type: String,
        default: '',
    }
});
    
defineEmits(['update:modelValue', 'blur', 'focus', 'change', 'keydown', 'keyup', 'keypress']);
</script>
