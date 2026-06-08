<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import { CheckCircle, XCircle, X } from 'lucide-vue-next';

const page = usePage();
const flash = computed(() => page.props.flash);
const showSuccess = ref(false);
const showError = ref(false);

watch(() => flash.value?.success, (val) => {
    if (val) {
        showSuccess.value = true;
        setTimeout(() => showSuccess.value = false, 5000);
    }
});

watch(() => flash.value?.error, (val) => {
    if (val) {
        showError.value = true;
        setTimeout(() => showError.value = false, 5000);
    }
});
</script>

<template>
    <!-- Success Flash -->
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="showSuccess"
            class="fixed top-4 right-4 z-[100] flex items-center gap-3 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm text-green-800 shadow-lg"
        >
            <CheckCircle class="h-5 w-5 text-green-500 shrink-0" />
            <span>{{ flash?.success }}</span>
            <button @click="showSuccess = false" class="ml-2">
                <X class="h-4 w-4 text-green-400 hover:text-green-600" />
            </button>
        </div>
    </Transition>

    <!-- Error Flash -->
    <Transition
        enter-active-class="transition ease-out duration-300"
        enter-from-class="translate-y-2 opacity-0"
        enter-to-class="translate-y-0 opacity-100"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="translate-y-0 opacity-100"
        leave-to-class="translate-y-2 opacity-0"
    >
        <div
            v-if="showError"
            class="fixed top-4 right-4 z-[100] flex items-center gap-3 rounded-lg bg-red-50 border border-red-200 px-4 py-3 text-sm text-red-800 shadow-lg"
        >
            <XCircle class="h-5 w-5 text-red-500 shrink-0" />
            <span>{{ flash?.error }}</span>
            <button @click="showError = false" class="ml-2">
                <X class="h-4 w-4 text-red-400 hover:text-red-600" />
            </button>
        </div>
    </Transition>
</template>
