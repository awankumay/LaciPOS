<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Card, CardContent } from '@/Components/ui/card';
import OnboardingProgress from '@/Components/OnboardingProgress.vue';
import OnboardingStep1 from '@/Components/OnboardingStep1.vue';
import OnboardingStep2 from '@/Components/OnboardingStep2.vue';
import OnboardingStep3 from '@/Components/OnboardingStep3.vue';
// Step 4, 5 akan di-import di T013
import { ref, reactive } from 'vue';

const props = defineProps({
    existingProfile: { type: Object, default: null },
});

const currentStep = ref(1);

// State onboarding — disimpan lokal, disubmit di step terakhir
const formData = reactive({
    store_name: props.existingProfile?.store_name || '',
    address: props.existingProfile?.address || '',
    phone: props.existingProfile?.phone || '',
    logo: null, // File object, akan dihandle di T012
    receipt_footer: props.existingProfile?.receipt_footer || 'Terima kasih sudah berkunjung!',
});

const nextStep = () => {
    if (currentStep.value < 5) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};
</script>

<template>
    <GuestLayout title="Setup Toko">
        <Card class="w-full max-w-lg mx-auto">
            <CardContent class="pt-6">
                <OnboardingProgress :current-step="currentStep" />

                <!-- Step 1: Nama Toko -->
                <OnboardingStep1
                    v-if="currentStep === 1"
                    v-model="formData.store_name"
                    @next="nextStep"
                />

                <!-- Step 2: Alamat -->
                <OnboardingStep2
                    v-if="currentStep === 2"
                    v-model="formData.address"
                    @next="nextStep"
                    @back="prevStep"
                />

                <!-- Step 3: Logo -->
                <OnboardingStep3
                    v-if="currentStep === 3"
                    v-model="formData.logo"
                    :existing-logo-url="existingProfile?.logo_url"
                    @next="nextStep"
                    @back="prevStep"
                />

                <!-- Step 4: Kontak + Step 5: Preview → T013 -->
                <div v-if="currentStep === 4 || currentStep === 5" class="text-center text-slate-400 py-8">
                    Step {{ currentStep }}: Akan diimplementasikan di T013
                </div>
            </CardContent>
        </Card>
    </GuestLayout>
</template>
