<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { UserPlus } from 'lucide-vue-next';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post('/register', {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout title="Daftar Akun">
        <Card>
            <CardHeader class="text-center">
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                    <UserPlus class="h-6 w-6 text-slate-700" />
                </div>
                <CardTitle class="text-xl">Buat Akun Baru</CardTitle>
                <CardDescription>
                    Daftarkan toko Anda untuk mulai menggunakan POS Desktop
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Nama -->
                    <div class="space-y-2">
                        <Label for="name">Nama Lengkap</Label>
                        <Input
                            id="name"
                            v-model="form.name"
                            type="text"
                            placeholder="Masukkan nama lengkap"
                            autofocus
                            required
                        />
                        <p v-if="form.errors.name" class="text-sm text-red-500">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="contoh@email.com"
                            required
                        />
                        <p v-if="form.errors.email" class="text-sm text-red-500">
                            {{ form.errors.email }}
                        </p>
                    </div>

                    <!-- Password -->
                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input
                            id="password"
                            v-model="form.password"
                            type="password"
                            placeholder="Minimal 8 karakter"
                            required
                        />
                        <p v-if="form.errors.password" class="text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Konfirmasi Password -->
                    <div class="space-y-2">
                        <Label for="password_confirmation">Konfirmasi Password</Label>
                        <Input
                            id="password_confirmation"
                            v-model="form.password_confirmation"
                            type="password"
                            placeholder="Ulangi password"
                            required
                        />
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Mendaftar...</span>
                        <span v-else>Daftar</span>
                    </Button>

                    <!-- Link ke Login -->
                    <p class="text-center text-sm text-slate-500">
                        Sudah punya akun?
                        <Link href="/login" class="text-slate-900 font-medium hover:underline">
                            Masuk di sini
                        </Link>
                    </p>
                </form>
            </CardContent>
        </Card>
    </GuestLayout>
</template>
