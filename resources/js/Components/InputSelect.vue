<script setup>
import { defineProps, defineEmits } from 'vue';

const props = defineProps({
    modelValue: {
        type: String,
        required: false,
        default: ''
    },
    options: {
        type: Array,
        required: false,
        default: []
    }
});

const emit = defineEmits(['update:modelValue']);

const updateValue = (event) => {
    emit('update:modelValue', event.target.value);
};
</script>

<template>
    <select @change="updateValue" class="flex-1 bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-r-lg focus:ring-blue-500 focus:border-blue-500 block p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500 mr-2">
        <option selected disabled class="text-sm text-gray-600 dark:text-gray-400">Selecione um repositório...</option>
        <option class="text-sm text-gray-600 dark:text-gray-400" v-for="option in props.options" :key="option.name" :value="option.name">{{ option.name }}</option>
    </select>
</template>

<style scoped>
</style>
