<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { LogIn, Loader2 } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
};
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
                            class="h-11"
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
                            class="h-11"
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
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

                    <!-- Link register -->
                    <p class="text-center text-sm text-[#5c6c7a]">
                        Belum punya akun?
                        <Link href="/register" class="font-semibold text-[#001e2b] hover:text-[#00684a] transition-colors">
                            Daftar di sini
                        </Link>
                    </p>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
