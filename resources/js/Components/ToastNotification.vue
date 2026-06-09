<script setup lang="ts">
import { useToast } from '@/composables/useToast';
import { X, CheckCircle2, AlertCircle, AlertTriangle, Info } from 'lucide-vue-next';
import { TransitionGroup } from 'vue';

const { toasts, remove } = useToast();

const icons = {
    success: CheckCircle2,
    error: AlertCircle,
    warning: AlertTriangle,
    info: Info,
};

const styles = {
    success: {
        container: 'bg-[#001e2b] border border-[#00684a]',
        icon: 'text-[#00ed64]',
        title: 'text-white',
        message: 'text-[#a8b3bc]',
        close: 'text-[#5c6c7a] hover:text-white',
        bar: 'bg-[#00ed64]',
    },
    error: {
        container: 'bg-[#001e2b] border border-red-800',
        icon: 'text-red-400',
        title: 'text-white',
        message: 'text-[#a8b3bc]',
        close: 'text-[#5c6c7a] hover:text-white',
        bar: 'bg-red-500',
    },
    warning: {
        container: 'bg-[#001e2b] border border-amber-700',
        icon: 'text-amber-400',
        title: 'text-white',
        message: 'text-[#a8b3bc]',
        close: 'text-[#5c6c7a] hover:text-white',
        bar: 'bg-amber-400',
    },
    info: {
        container: 'bg-[#001e2b] border border-[#1c2d38]',
        icon: 'text-blue-400',
        title: 'text-white',
        message: 'text-[#a8b3bc]',
        close: 'text-[#5c6c7a] hover:text-white',
        bar: 'bg-blue-400',
    },
};
</script>

<template>
    <!-- Toast Portal — fixed di sudut kanan bawah -->
    <div class="fixed bottom-6 right-6 z-50 flex flex-col gap-3 w-80" role="region" aria-label="Notifikasi">
        <TransitionGroup
            name="toast"
            tag="div"
            class="flex flex-col gap-3"
        >
            <div
                v-for="toast in toasts"
                :key="toast.id"
                :class="[
                    'relative flex items-start gap-3 rounded-xl px-4 py-3 shadow-2xl overflow-hidden cursor-pointer select-none',
                    styles[toast.type].container,
                ]"
                @click="remove(toast.id)"
            >
                <!-- Colored top bar -->
                <div :class="['absolute top-0 left-0 right-0 h-0.5', styles[toast.type].bar]" />

                <!-- Icon -->
                <component
                    :is="icons[toast.type]"
                    :class="['mt-0.5 h-5 w-5 shrink-0', styles[toast.type].icon]"
                />

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <p v-if="toast.title" :class="['text-sm font-semibold leading-5', styles[toast.type].title]">
                        {{ toast.title }}
                    </p>
                    <p :class="['text-sm leading-5', toast.title ? 'mt-0.5' : '', styles[toast.type].message]">
                        {{ toast.message }}
                    </p>
                </div>

                <!-- Close button -->
                <button
                    :class="['shrink-0 transition-colors', styles[toast.type].close]"
                    @click.stop="remove(toast.id)"
                    aria-label="Tutup notifikasi"
                >
                    <X class="h-4 w-4" />
                </button>
            </div>
        </TransitionGroup>
    </div>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.toast-enter-from {
    opacity: 0;
    transform: translateX(100%) scale(0.95);
}
.toast-leave-to {
    opacity: 0;
    transform: translateX(100%) scale(0.95);
}
.toast-move {
    transition: transform 0.3s ease;
}
</style>
