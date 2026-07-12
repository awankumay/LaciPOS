<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import OnboardingProgress from '@/Components/OnboardingProgress.vue';
import OnboardingStep1 from '@/Components/OnboardingStep1.vue';
import OnboardingStep2 from '@/Components/OnboardingStep2.vue';
import OnboardingStep3 from '@/Components/OnboardingStep3.vue';
import OnboardingStep4 from '@/Components/OnboardingStep4.vue';
import OnboardingStep5 from '@/Components/OnboardingStep5.vue';
import OnboardingStep6 from '@/Components/OnboardingStep6.vue';
import { ref, reactive, watch, onUnmounted } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    existingProfile: { type: Object, default: null },
});

const currentStep = ref(1);
const isSubmitting = ref(false);

const formData = reactive({
    store_name: props.existingProfile?.store_name || '',
    address: props.existingProfile?.address || '',
    phone: props.existingProfile?.phone || '',
    logo: null,
    receipt_footer: props.existingProfile?.receipt_footer || 'Terima kasih sudah berbelanja di toko kami!',
    timezone: props.existingProfile?.timezone || 'Asia/Jakarta',
    security_question: '',
    security_answer: '',
});

// Preview URL untuk logo
const logoPreviewUrl = ref(props.existingProfile?.logo_url || null);

watch(() => formData.logo, (newLogo, oldLogo) => {
    const currentUrl = logoPreviewUrl.value;
    if (oldLogo && currentUrl && currentUrl.startsWith('blob:')) {
        URL.revokeObjectURL(currentUrl);
    }
    if (newLogo) {
        logoPreviewUrl.value = URL.createObjectURL(newLogo);
    } else {
        logoPreviewUrl.value = props.existingProfile?.logo_url || null;
    }
});

onUnmounted(() => {
    const currentUrl = logoPreviewUrl.value;
    if (currentUrl && currentUrl.startsWith('blob:')) {
        URL.revokeObjectURL(currentUrl);
    }
});

const nextStep = () => {
    if (currentStep.value < 6) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submitOnboarding = () => {
    isSubmitting.value = true;

    const data = new FormData();
    data.append('store_name', formData.store_name);
    if (formData.address) data.append('address', formData.address);
    if (formData.phone) data.append('phone', formData.phone);
    if (formData.logo) data.append('logo', formData.logo);
    if (formData.receipt_footer) data.append('receipt_footer', formData.receipt_footer);
    if (formData.timezone) data.append('timezone', formData.timezone);
    if (formData.security_question) data.append('security_question', formData.security_question);
    if (formData.security_answer) data.append('security_answer', formData.security_answer);

    router.post('/onboarding', data, {
        forceFormData: true,
        onSuccess: () => {
            isSubmitting.value = false;
        },
        onError: () => {
            isSubmitting.value = false;
        },
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <GuestLayout title="Setup Toko">
        <!-- Wizard Card -->
        <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_4px_12px_rgba(0,30,43,0.08)]">
            <!-- Card Header: teal band -->
            <div class="rounded-t-2xl bg-[#001e2b] px-8 py-6">
                <p class="text-xs font-semibold uppercase tracking-widest text-[#00ed64] mb-1">Setup Toko</p>
                <h1 class="text-2xl font-semibold leading-snug text-white">
                    Ayo setup toko Anda 🚀
                </h1>
                <p class="mt-1 text-sm text-[#a8b3bc]">
                    Selesaikan {{ 6 }} langkah berikut untuk mulai berjualan.
                </p>
            </div>

            <!-- Progress Bar -->
            <div class="border-b border-[#e1e5e8] px-8 pt-5 pb-9">
                <OnboardingProgress :current-step="currentStep" />
            </div>

            <!-- Step Content -->
            <div class="px-8 py-8">
                <Transition name="step" mode="out-in">
                    <OnboardingStep1
                        v-if="currentStep === 1"
                        key="step1"
                        v-model="formData.store_name"
                        @next="nextStep"
                    />

                    <OnboardingStep2
                        v-else-if="currentStep === 2"
                        key="step2"
                        v-model:address="formData.address"
                        v-model:timezone="formData.timezone"
                        @next="nextStep"
                        @back="prevStep"
                    />

                    <OnboardingStep3
                        v-else-if="currentStep === 3"
                        key="step3"
                        v-model="formData.logo"
                        :existing-logo-url="existingProfile?.logo_url"
                        @next="nextStep"
                        @back="prevStep"
                    />

                    <OnboardingStep4
                        v-else-if="currentStep === 4"
                        key="step4"
                        :phone="formData.phone"
                        :receipt-footer="formData.receipt_footer"
                        @update:phone="formData.phone = $event"
                        @update:receipt-footer="formData.receipt_footer = $event"
                        @next="nextStep"
                        @back="prevStep"
                    />

                    <OnboardingStep5
                        v-else-if="currentStep === 5"
                        key="step5"
                        v-model="formData.security_question"
                        v-model:security-answer="formData.security_answer"
                        @next="nextStep"
                        @back="prevStep"
                    />

                    <OnboardingStep6
                        v-else-if="currentStep === 6"
                        key="step6"
                        :store-name="formData.store_name"
                        :address="formData.address"
                        :phone="formData.phone"
                        :logo-preview-url="logoPreviewUrl"
                        :receipt-footer="formData.receipt_footer"
                        :is-submitting="isSubmitting"
                        @back="prevStep"
                        @submit="submitOnboarding"
                    />
                </Transition>
            </div>
        </div>

        <!-- Footer note -->
        <p class="mt-5 text-center text-xs text-[#7c8c9a]">
            Data Anda tersimpan secara lokal dan aman.
        </p>
    </GuestLayout>
</template>

<style scoped>
.step-enter-active,
.step-leave-active {
    transition: all 0.2s ease;
}
.step-enter-from {
    opacity: 0;
    transform: translateX(12px);
}
.step-leave-to {
    opacity: 0;
    transform: translateX(-12px);
}
</style>
