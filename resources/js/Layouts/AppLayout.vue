<script setup>
import Sidebar from '@/Components/Sidebar.vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { onMounted, watch } from 'vue';
import { useToast } from '@/composables/useToast';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const toast = useToast();

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

    <div class="min-h-screen bg-[#f4f7f6]">
        <!-- Sidebar -->
        <Sidebar />

        <!-- Main Content -->
        <main class="pl-64 min-h-screen">
            <!-- Page Header slot -->
            <div
                v-if="$slots.header"
                class="sticky top-0 z-30 flex h-16 items-center border-b border-[#e1e5e8] bg-white/90 px-8 backdrop-blur-sm"
            >
                <slot name="header" />
            </div>

            <!-- Page Content -->
            <div class="px-8 py-8">
                <slot />
            </div>
        </main>

        <!-- Toast Notification -->
        <ToastNotification />
    </div>
</template>
