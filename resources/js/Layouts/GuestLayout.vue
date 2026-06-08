<script setup>
import { Head } from '@inertiajs/vue3';
import { Store } from 'lucide-vue-next';

defineProps({
    title: { type: String, default: '' },
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-slate-50 flex flex-col items-center justify-center p-4">
        <!-- Logo -->
        <div class="mb-8 flex items-center gap-2">
            <Store class="h-8 w-8 text-slate-800" />
            <span class="text-2xl font-bold text-slate-800">POS Desktop</span>
        </div>

        <!-- Content Card -->
        <div class="w-full max-w-md">
            <slot />
        </div>
    </div>
</template>
