<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import OnboardingProgress from '@/Components/OnboardingProgress.vue';
import OnboardingStep1 from '@/Components/OnboardingStep1.vue';
import OnboardingStep2 from '@/Components/OnboardingStep2.vue';
import OnboardingStep3 from '@/Components/OnboardingStep3.vue';
import OnboardingStep4 from '@/Components/OnboardingStep4.vue';
import OnboardingStep5 from '@/Components/OnboardingStep5.vue';
import { ref, reactive, computed } from 'vue';
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
    receipt_footer: props.existingProfile?.receipt_footer || 'Terima kasih sudah berkunjung!',
});

// Preview URL untuk logo
const logoPreviewUrl = computed(() => {
    if (formData.logo) {
        return URL.createObjectURL(formData.logo);
    }
    return props.existingProfile?.logo_url || null;
});

const nextStep = () => {
    if (currentStep.value < 5) currentStep.value++;
};

const prevStep = () => {
    if (currentStep.value > 1) currentStep.value--;
};

const submitOnboarding = () => {
    isSubmitting.value = true;

    // Gunakan FormData untuk upload file
    const data = new FormData();
    data.append('store_name', formData.store_name);
    if (formData.address) data.append('address', formData.address);
    if (formData.phone) data.append('phone', formData.phone);
    if (formData.logo) data.append('logo', formData.logo);
    if (formData.receipt_footer) data.append('receipt_footer', formData.receipt_footer);

    router.post('/onboarding', data, {
        forceFormData: true,
        onFinish: () => {
            isSubmitting.value = false;
        },
    });
};
</script>

<template>
    <GuestLayout title="Setup Toko">
        <Card class="w-full max-w-lg mx-auto">
            <CardContent class="pt-6">
                <OnboardingProgress :current-step="currentStep" />

                <OnboardingStep1
                    v-if="currentStep === 1"
                    v-model="formData.store_name"
                    @next="nextStep"
                />

                <OnboardingStep2
                    v-if="currentStep === 2"
                    v-model="formData.address"
                    @next="nextStep"
                    @back="prevStep"
                />

                <OnboardingStep3
                    v-if="currentStep === 3"
                    v-model="formData.logo"
                    :existing-logo-url="existingProfile?.logo_url"
                    @next="nextStep"
                    @back="prevStep"
                />

                <OnboardingStep4
                    v-if="currentStep === 4"
                    :phone="formData.phone"
                    :receipt-footer="formData.receipt_footer"
                    @update:phone="formData.phone = $event"
                    @update:receipt-footer="formData.receipt_footer = $event"
                    @next="nextStep"
                    @back="prevStep"
                />

                <OnboardingStep5
                    v-if="currentStep === 5"
                    :store-name="formData.store_name"
                    :address="formData.address"
                    :phone="formData.phone"
                    :logo-preview-url="logoPreviewUrl"
                    :receipt-footer="formData.receipt_footer"
                    :is-submitting="isSubmitting"
                    @back="prevStep"
                    @submit="submitOnboarding"
                />
            </CardContent>
        </Card>
    </GuestLayout>
</template>
