<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { useForm, Link } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { LogIn } from 'lucide-vue-next';

const form = useForm({
    email: '',
    password: '',
});

const submit = () => {
    form.post('/login', {
        onFinish: () => {
            form.reset('password');
        },
    });
};
</script>

<template>
    <GuestLayout title="Masuk">
        <Card>
            <CardHeader class="text-center">
                <div class="mx-auto mb-2 flex h-12 w-12 items-center justify-center rounded-full bg-slate-100">
                    <LogIn class="h-6 w-6 text-slate-700" />
                </div>
                <CardTitle class="text-xl">Masuk ke Akun Anda</CardTitle>
                <CardDescription>
                    Masukkan email dan password untuk melanjutkan
                </CardDescription>
            </CardHeader>
            <CardContent>
                <form @submit.prevent="submit" class="space-y-4">
                    <!-- Email -->
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input
                            id="email"
                            v-model="form.email"
                            type="email"
                            placeholder="contoh@email.com"
                            autofocus
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
                            placeholder="Masukkan password"
                            required
                        />
                        <p v-if="form.errors.password" class="text-sm text-red-500">
                            {{ form.errors.password }}
                        </p>
                    </div>

                    <!-- Submit Button -->
                    <Button
                        type="submit"
                        class="w-full"
                        :disabled="form.processing"
                    >
                        <span v-if="form.processing">Memproses...</span>
                        <span v-else>Masuk</span>
                    </Button>

                    <!-- Link ke Register -->
                    <p class="text-center text-sm text-slate-500">
                        Belum punya akun?
                        <Link href="/register" class="text-slate-900 font-medium hover:underline">
                            Daftar di sini
                        </Link>
                    </p>
                </form>
            </CardContent>
        </Card>
    </GuestLayout>
</template>
