<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import FlashMessage from '@/Components/FlashMessage.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { useToast } from '@/composables/useToast';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const toast = useToast();

// Trigger toast dari flash message Inertia
const handleFlash = () => {
    const flash = page.props.flash;
    if (flash?.success) toast.success(flash.success, 'Berhasil');
    if (flash?.error) toast.error(flash.error, 'Terjadi Kesalahan');
};

onMounted(handleFlash);
watch(() => page.props.flash, handleFlash, { deep: true });
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

        <!-- Flash Messages (legacy, tetap dipertahankan) -->
        <FlashMessage />

        <!-- Toast Notification -->
        <ToastNotification />
    </div>
</template>
