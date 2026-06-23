<script setup>
import { Head, usePage } from '@inertiajs/vue3';
import { Store } from 'lucide-vue-next';
import { onMounted, watch } from 'vue';
import ToastNotification from '@/Components/ToastNotification.vue';
import { useToast } from '@/composables/useToast';

defineProps({
    title: { type: String, default: '' },
});

const page = usePage();
const toast = useToast();

// Trigger toast ketika ada flash message dari Inertia
const handleFlash = () => {
    const flash = page.props.flash;
    if (flash?.success) toast.success(flash.success, 'Berhasil');
    if (flash?.print_success) toast.success(flash.print_success, 'Berhasil');
    if (flash?.error) toast.error(flash.error, 'Terjadi Kesalahan');
    if (flash?.warning) toast.warning(flash.warning, 'Perhatian');
};

onMounted(handleFlash);
watch(() => page.props.flash, handleFlash, { deep: true });
</script>

<template>
    <Head :title="title" />

    <div class="min-h-screen bg-[#f4f7f6] flex flex-col items-center justify-center p-6">


        <!-- Content Slot -->
        <div class="w-full max-w-lg">
            <slot />
        </div>

        <!-- Toast Notification -->
        <ToastNotification />
    </div>
</template>
