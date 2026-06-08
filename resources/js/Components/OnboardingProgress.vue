<script setup>
import { Check } from 'lucide-vue-next';

const props = defineProps({
    currentStep: { type: Number, required: true },
    totalSteps: { type: Number, default: 5 },
});

const steps = [
    { number: 1, label: 'Nama Toko' },
    { number: 2, label: 'Alamat' },
    { number: 3, label: 'Logo' },
    { number: 4, label: 'Kontak' },
    { number: 5, label: 'Preview' },
];
</script>

<template>
    <div class="flex items-center justify-center gap-2 mb-8">
        <template v-for="(step, index) in steps" :key="step.number">
            <!-- Step indicator -->
            <div class="flex items-center gap-2">
                <div
                    :class="[
                        'flex h-8 w-8 items-center justify-center rounded-full text-sm font-medium transition-colors',
                        currentStep > step.number
                            ? 'bg-green-500 text-white'
                            : currentStep === step.number
                            ? 'bg-slate-900 text-white'
                            : 'bg-slate-200 text-slate-500'
                    ]"
                >
                    <Check v-if="currentStep > step.number" class="h-4 w-4" />
                    <span v-else>{{ step.number }}</span>
                </div>
                <span
                    :class="[
                        'text-sm hidden sm:inline',
                        currentStep >= step.number ? 'text-slate-900 font-medium' : 'text-slate-400'
                    ]"
                >
                    {{ step.label }}
                </span>
            </div>

            <!-- Connector line -->
            <div
                v-if="index < steps.length - 1"
                :class="[
                    'h-px w-8 transition-colors',
                    currentStep > step.number ? 'bg-green-500' : 'bg-slate-200'
                ]"
            />
        </template>
    </div>
</template>
