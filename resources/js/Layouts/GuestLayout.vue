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
        <!-- Logo / Brand -->
        <div class="mb-10 flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-[#001e2b]">
                <Store class="h-5 w-5 text-[#00ed64]" />
            </div>
            <span class="text-xl font-bold tracking-tight text-[#001e2b]">POS Desktop</span>
        </div>

        <!-- Content Slot -->
        <div class="w-full max-w-lg">
            <slot />
        </div>

        <!-- Toast Notification -->
        <ToastNotification />
    </div>
</template>
