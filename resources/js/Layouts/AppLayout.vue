<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import { Head } from '@inertiajs/vue3';

defineProps({
    title: { type: String, default: '' },
});
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-slate-50">
        <!-- Sidebar -->
        <Sidebar />

        <!-- Main Content -->
        <main class="pl-64">
            <!-- Page Header (opsional, diisi via slot) -->
            <div v-if="$slots.header" class="border-b border-slate-200 bg-white px-8 py-5">
                <slot name="header" />
            </div>

            <!-- Page Content -->
            <div class="p-8">
                <slot />
            </div>
        </main>

        <!-- Flash Messages -->
        <FlashMessage />
    </div>
</template>
