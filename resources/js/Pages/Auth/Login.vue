<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { LogIn, Loader2 } from 'lucide-vue-next';
import { useToast } from '@/composables/useToast';
import { watch } from 'vue';

const toast = useToast();

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};

watch(() => form.errors, (errors) => {
    const firstError = Object.values(errors).find(Boolean);
    if (firstError) toast.error(firstError);
}, { deep: true });
</script>

<template>
    <GuestLayout title="Masuk">
        <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_4px_12px_rgba(0,30,43,0.08)]">
            <!-- Header band -->
            <div class="rounded-t-2xl bg-[#001e2b] px-8 py-7 text-start">
                <img src="/assets/logo/logo.png" alt="Logo" class=" mb-0 h-16 w-auto object-contain" />
                <h1 class="text-xl font-bold text-white">Masuk ke Akun Anda</h1>
                <p class="mt-1 text-sm text-[#a8b3bc]">Masukkan email dan password untuk melanjutkan.</p>
            </div>

            <!-- Form -->
            <div class="px-8 py-8">
                <form @submit.prevent="submit" class="space-y-6">
                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium text-[#1c2d38]">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="contoh@email.com"
                            autofocus
                            required
                            :class="['h-11 rounded-xl border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10', form.errors.email ? 'border-red-500 focus-visible:ring-red-400' : '']"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <Label for="password" class="text-sm font-medium text-[#1c2d38]">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Masukkan password"
                            required
                            :class="['h-11 rounded-xl border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10', form.errors.password ? 'border-red-500 focus-visible:ring-red-400' : '']"
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Lupa Password -->
                    <div class="text-right">
                        <Link
                            href="/forgot-password"
                            class="text-xs font-medium text-[#5c6c7a] hover:text-[#001e2b] transition-colors"
                        >
                            Lupa Password?
                        </Link>
                    </div>

                    <!-- Submit -->
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
                        {{ form.processing ? 'Memproses...' : 'Masuk' }}
                    </button>

                </form>
            </div>
        </div>
    </GuestLayout>
</template>
