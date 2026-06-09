<script setup>
defineProps({ product: Object });
defineEmits(['click']);
</script>
<template>
    <div
        class="border border-slate-200 rounded-lg p-3 cursor-pointer hover:border-slate-400"
        @click="$emit('click')"
    >
        <p class="font-medium text-sm">{{ product.name }}</p>
    </div>
</template>
