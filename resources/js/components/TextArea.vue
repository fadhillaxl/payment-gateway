<template>
    <div class="relative">
        <div v-if="label" class="block text-sm font-medium text-gray-700">
            {{ label }}
        </div>
        <textarea
            :name="name"
            :value="modelValue"
            :placeholder="placeholder"
            :class="classes"
            :disabled="disabled"
            :readonly="readonly"
            :rows="rows"
            :cols="cols"
            @input="$emit('update:modelValue', $event.target.value)"
            @blur="$emit('blur')"
            @focus="$emit('focus')"
            @change="$emit('change')"
            @keydown="$emit('keydown')"
            @keyup="$emit('keyup')"
            @keypress="$emit('keypress')"
        ></textarea>
    </div>
</template>

<script setup>
defineProps({
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
    rows: {
        type: Number,
        default: 3,
    },
    cols: {
        type: Number,
        default: 50,
    },
    label: {
        type: String,
        default: '',
    }
});

defineEmits(['update:modelValue', 'blur', 'focus', 'change', 'keydown', 'keyup', 'keypress']);
</script>   
    