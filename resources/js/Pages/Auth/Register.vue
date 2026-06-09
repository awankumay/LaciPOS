<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { UserPlus, Loader2 } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout title="Daftar Akun">
        <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_4px_12px_rgba(0,30,43,0.08)]">
            <!-- Header band -->
            <div class="rounded-t-2xl bg-[#001e2b] px-8 py-7 text-center">
                <div class="mx-auto mb-3 flex h-12 w-12 items-center justify-center rounded-xl bg-[#00ed64]">
                    <UserPlus class="h-6 w-6 text-[#001e2b]" />
                </div>
                <h1 class="text-xl font-bold text-white">Buat Akun Baru</h1>
                <p class="mt-1 text-sm text-[#a8b3bc]">Daftarkan toko Anda untuk mulai menggunakan POS Desktop.</p>
            </div>

            <!-- Form -->
            <div class="px-8 py-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Nama -->
                    <div class="space-y-2">
                        <Label for="name" class="text-sm font-medium text-[#1c2d38]">Nama Lengkap</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan nama lengkap"
                            autofocus
                            required
                            class="h-11"
                        />
                        <p v-if="form.errors.name" class="text-xs text-red-500">{{ form.errors.name }}</p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email" class="text-sm font-medium text-[#1c2d38]">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="contoh@email.com"
                            required
                            class="h-11"
                        />
                        <p v-if="form.errors.email" class="text-xs text-red-500">{{ form.errors.email }}</p>
                    </div>

                    <!-- Divider -->
                    <div class="relative">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-[#eceff1]" />
                        </div>
                        <div class="relative flex justify-center">
                            <span class="bg-white px-3 text-xs text-[#a8b3bc]">Keamanan Akun</span>
                        </div>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <Label for="password" class="text-sm font-medium text-[#1c2d38]">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            required
                            class="h-11"
                        />
                        <p v-if="form.errors.password" class="text-xs text-red-500">{{ form.errors.password }}</p>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-2">
                        <Label for="password_confirmation" class="text-sm font-medium text-[#1c2d38]">Konfirmasi Password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Ulangi password"
                            required
                            class="h-11"
                        />
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
                        {{ form.processing ? 'Mendaftar...' : 'Buat Akun' }}
                    </button>

                    <!-- Link login -->
                    <p class="text-center text-sm text-[#5c6c7a]">
                        Sudah punya akun?
                        <Link href="/login" class="font-semibold text-[#001e2b] hover:text-[#00684a] transition-colors">
                            Masuk di sini
                        </Link>
                    </p>
                </form>
            </div>
        </div>
    </GuestLayout>
</template>
