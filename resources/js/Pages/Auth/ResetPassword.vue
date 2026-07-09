<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Shield, Key, Loader2, ArrowLeft } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import { ref, watch } from 'vue';

const toast = useToast();

const props = defineProps({
    email: { type: String, required: true },
    security_question: { type: String, required: true },
});

const recoveryMethod = ref('question');

const form = useForm({
    email: props.email,
    password: '',
    password_confirmation: '',
    security_answer: '',
    recovery_code: '',
});

const submit = () => {
    if (recoveryMethod.value === 'question') {
        form.recovery_code = '';
    } else {
        form.security_answer = '';
    }
    form.post('/forgot-password/reset', {
        preserveState: true,
    });
};

watch(() => form.errors, (errors) => {
    const firstError = Object.values(errors).find(Boolean);
    if (firstError) toast.error(firstError);
}, { deep: true });
</script>

<template>
    <GuestLayout title="Reset Password">
        <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_4px_12px_rgba(0,30,43,0.08)]">
            <div class="rounded-t-2xl bg-[#001e2b] px-8 py-7">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#00ed64]/20">
                    <Shield class="h-7 w-7 text-[#00ed64]" />
                </div>
                <h1 class="text-center text-xl font-bold text-white">Reset Password</h1>
                <p class="mt-1 text-center text-sm text-[#a8b3bc]">
                    Verifikasi identitas Anda untuk mereset password.
                </p>
            </div>

            <div class="px-8 py-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <div class="rounded-xl bg-[#f4f7f6] px-4 py-3">
                        <p class="text-xs font-medium text-[#5c6c7a]">Email</p>
                        <p class="text-sm font-semibold text-[#001e2b]">{{ email }}</p>
                    </div>

                    <div class="flex gap-2">
                        <button
                            type="button"
                            @click="recoveryMethod = 'question'"
                            :class="[
                                'flex-1 rounded-xl py-2 text-xs font-semibold transition-colors',
                                recoveryMethod === 'question'
                                    ? 'bg-[#001e2b] text-white'
                                    : 'bg-[#eceff1] text-[#5c6c7a] hover:bg-[#d0d7dd]',
                            ]"
                        >
                            Jawab Pertanyaan
                        </button>
                        <button
                            type="button"
                            @click="recoveryMethod = 'code'"
                            :class="[
                                'flex-1 rounded-xl py-2 text-xs font-semibold transition-colors',
                                recoveryMethod === 'code'
                                    ? 'bg-[#001e2b] text-white'
                                    : 'bg-[#eceff1] text-[#5c6c7a] hover:bg-[#d0d7dd]',
                            ]"
                        >
                            Kode Pemulihan
                        </button>
                    </div>

                    <template v-if="recoveryMethod === 'question'">
                        <div class="rounded-xl border border-[#e1e5e8] bg-[#fafbfc] p-4">
                            <p class="text-xs font-medium text-[#7c8c9a]">Pertanyaan Keamanan</p>
                            <p class="mt-1 text-sm font-medium text-[#001e2b]">{{ security_question }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="security_answer" class="text-sm font-medium text-[#1c2d38]">Jawaban Anda</Label>
                            <Input
                                id="security_answer"
                                v-model="form.security_answer"
                                type="text"
                                placeholder="Masukkan jawaban pertanyaan keamanan"
                                autofocus
                                :class="['h-11', form.errors.security_answer ? 'border-red-500 focus-visible:ring-red-400' : '']"
                            />
                            <p v-if="form.errors.security_answer" class="text-xs text-red-500">{{ form.errors.security_answer }}</p>
                        </div>
                    </template>

                    <template v-else>
                        <div class="rounded-xl border border-[#e1e5e8] bg-[#fafbfc] p-4">
                            <p class="text-xs font-medium text-[#7c8c9a]">Kode Pemulihan</p>
                            <p class="mt-1 text-xs text-[#5c6c7a]">
                                Masukkan salah satu kode pemulihan yang Anda simpan saat setup. Setiap kode hanya bisa digunakan sekali.
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="recovery_code" class="text-sm font-medium text-[#1c2d38]">Kode Pemulihan</Label>
                            <Input
                                id="recovery_code"
                                v-model="form.recovery_code"
                                type="text"
                                placeholder="Contoh: XXXX-XXXX"
                                autofocus
                                :class="['h-11', form.errors.recovery_code ? 'border-red-500 focus-visible:ring-red-400' : '']"
                            />
                            <p v-if="form.errors.recovery_code" class="text-xs text-red-500">{{ form.errors.recovery_code }}</p>
                        </div>
                    </template>

                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-[#eceff1]" />
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-white px-3 text-xs text-[#a8b3bc]">Password Baru</span>
                        </div>
                    </div>

                    <div class="space-y-2">
                        <Label for="password" class="text-sm font-medium text-[#1c2d38]">Password Baru</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            required
                            :class="['h-11', form.errors.password ? 'border-red-500 focus-visible:ring-red-400' : '']"
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <div class="space-y-2">
                        <Label for="password_confirmation" class="text-sm font-medium text-[#1c2d38]">Konfirmasi Password Baru</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Ulangi password baru"
                            required
                            class="h-11"
                        />
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
                        <Key v-else class="h-4 w-4" />
                        {{ form.processing ? 'Menyimpan...' : 'Reset Password' }}
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
