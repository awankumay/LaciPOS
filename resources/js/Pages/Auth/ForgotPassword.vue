<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Lock, Loader2, ArrowLeft } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import { watch } from 'vue';

const toast = useToast();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post('/forgot-password/verify-email', {
        preserveState: true,
    });
};

watch(() => form.errors, (errors) => {
    const firstError = Object.values(errors).find(Boolean);
    if (firstError) toast.error(firstError);
}, { deep: true });
</script>

<template>
    <GuestLayout title="Lupa Password">
        <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_4px_12px_rgba(0,30,43,0.08)]">
            <div class="rounded-t-2xl bg-[#001e2b] px-8 py-7">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#00ed64]/20">
                    <Lock class="h-7 w-7 text-[#00ed64]" />
                </div>
                <h1 class="text-center text-xl font-bold text-white">Lupa Password</h1>
                <p class="mt-1 text-center text-sm text-[#a8b3bc]">
                    Masukkan email pemilik toko untuk memulihkan akun.
                </p>
            </div>

            <div class="px-8 py-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium text-[#1c2d38]">Email Pemilik Toko</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="contoh@email.com"
                            autofocus
                            required
                            :class="['h-11', form.errors.email ? 'border-red-500 focus-visible:ring-red-400' : '']"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <button
                        type="submit"
                        :disabled="form.processing"
                        :class="[
                            'flex w-full items-center justify-center gap-2 rounded-full py-3 text-sm font-semibold transition-colors',
                            form.processing
                                ? 'bg-[#e1e5e8] text-[#a8b3bc] cursor-not-allowed'
                                : 'bg-[#00ed64] text-[#001e2b] hover:bg-[#00b545] active:bg-[#008c34]',
                        ]"
                    >
                        <Loader2 v-if="form.processing" class="h-4 w-4 animate-spin" />
                        {{ form.processing ? 'Memverifikasi...' : 'Verifikasi Email' }}
                    </button>

                    <Link
                        href="/login"
                        class="flex items-center justify-center gap-2 text-sm text-[#5c6c7a] hover:text-[#001e2b] transition-colors"
                    >
                        <ArrowLeft class="h-4 w-4" />
                        Kembali ke Login
                    </Link>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
