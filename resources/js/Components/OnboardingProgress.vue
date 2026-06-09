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
    <div class="flex items-center" role="list" aria-label="Langkah setup">
        <template v-for="(step, index) in steps" :key="step.number">
            <!-- Step indicator -->
            <div class="flex flex-col items-center" role="listitem">
                <!-- Circle -->
                <div
                    :class="[
                        'flex h-8 w-8 items-center justify-center rounded-full text-xs font-bold transition-all duration-200',
                        currentStep > step.number
                            ? 'bg-[#00ed64] text-[#001e2b]'
                            : currentStep === step.number
                            ? 'bg-[#001e2b] text-white ring-2 ring-[#00ed64] ring-offset-2'
                            : 'bg-[#eceff1] text-[#7c8c9a]',
                    ]"
                >
                    <Check v-if="currentStep > step.number" class="h-4 w-4 stroke-[2.5]" />
                    <span v-else>{{ step.number }}</span>
                </div>
                <!-- Label -->
                <span
                    :class="[
                        'mt-1.5 text-[10px] font-semibold uppercase tracking-wide hidden sm:block',
                        currentStep > step.number
                            ? 'text-[#00684a]'
                            : currentStep === step.number
                            ? 'text-[#001e2b]'
                            : 'text-[#a8b3bc]',
                    ]"
                >
                    {{ step.label }}
                </span>
            </div>

            <!-- Connector line -->
            <div
                v-if="index < steps.length - 1"
                class="relative mx-2 flex-1"
            >
                <!-- Background track -->
                <div class="h-px w-full bg-[#eceff1]" />
                <!-- Progress fill -->
                <div
                    :class="[
                        'absolute inset-0 h-px transition-all duration-300',
                        currentStep > step.number ? 'bg-[#00ed64]' : 'bg-transparent',
                    ]"
                />
            </div>
        </template>
    </div>
</template>
