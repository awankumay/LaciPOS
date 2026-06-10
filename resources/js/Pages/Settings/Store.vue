<script setup>
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
import { Input } from '@/Components/ui/input';
import { Card, CardHeader, CardTitle, CardDescription, CardContent, CardFooter } from '@/Components/ui/card';
import { UploadCloud, X } from 'lucide-vue-next';

const props = defineProps({
    profile: {
        type: Object,
        default: () => ({}),
    },
});

const form = useForm({
    store_name: props.profile?.store_name || '',
    address: props.profile?.address || '',
    phone: props.profile?.phone || '',
    logo: null,
    remove_logo: false,
    receipt_footer: props.profile?.receipt_footer || 'Terima kasih sudah berkunjung!',
});

const logoPreviewUrl = ref(props.profile?.logo_url || null);

const handleLogoChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        form.logo = file;
        form.remove_logo = false;
        logoPreviewUrl.value = URL.createObjectURL(file);
    }
};

const removeLogo = () => {
    form.logo = null;
    form.remove_logo = true;
    logoPreviewUrl.value = null;
};

const submit = () => {
    form.post('/settings/store', {
        preserveScroll: true,
        forceFormData: true,
    });
};
</script>

<template>
    <SettingsLayout title="Informasi Toko">
        <div class="flex items-center justify-between space-y-2">
            <h2 class="text-2xl font-bold tracking-tight text-[#001e2b]">Informasi Toko</h2>
        </div>

            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <Card class="col-span-2">
                    <form @submit.prevent="submit">
                        <CardHeader>
                            <CardTitle>Profil Toko</CardTitle>
                            <CardDescription>
                                Informasi detail mengenai toko Anda yang akan muncul di struk transaksi.
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="space-y-2">
                                <Label for="store_name">Nama Toko <span class="text-destructive">*</span></Label>
                                <Input
                                    id="store_name"
                                    v-model="form.store_name"
                                    placeholder="Contoh: Toko Maju Jaya"
                                    required
                                />
                                <p v-if="form.errors.store_name" class="text-sm text-destructive">{{ form.errors.store_name }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="address">Alamat Lengkap</Label>
                                <Input
                                    id="address"
                                    v-model="form.address"
                                    placeholder="Contoh: Jl. Merdeka No. 123, Jakarta"
                                />
                                <p v-if="form.errors.address" class="text-sm text-destructive">{{ form.errors.address }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">Nomor Telepon</Label>
                                <Input
                                    id="phone"
                                    v-model="form.phone"
                                    placeholder="Contoh: 081234567890"
                                />
                                <p v-if="form.errors.phone" class="text-sm text-destructive">{{ form.errors.phone }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label>Logo Toko</Label>
                                <div class="mt-2 flex items-center gap-6">
                                    <!-- Preview area -->
                                    <div
                                        class="relative flex h-24 w-24 shrink-0 items-center justify-center overflow-hidden rounded-2xl border-2 border-dashed border-[#c1ccd6] bg-[#f8fafc]"
                                    >
                                        <img
                                            v-if="logoPreviewUrl"
                                            :src="logoPreviewUrl"
                                            class="h-full w-full object-cover"
                                            alt="Logo Toko"
                                        />
                                        <UploadCloud v-else class="h-8 w-8 text-[#a8b3bc]" />

                                        <button
                                            v-if="logoPreviewUrl"
                                            type="button"
                                            @click="removeLogo"
                                            class="absolute right-0 top-0 translate-x-1/3 -translate-y-1/3 rounded-full bg-white p-1 text-[#ff3b3b] shadow-sm hover:bg-[#fff0f0] border border-[#ff3b3b]"
                                        >
                                            <X class="h-4 w-4" />
                                        </button>
                                    </div>

                                    <!-- Upload button -->
                                    <div>
                                        <label
                                            class="inline-flex cursor-pointer items-center gap-2 rounded-full border border-[#c1ccd6] bg-white px-5 py-2.5 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]"
                                        >
                                            <UploadCloud class="h-4 w-4" />
                                            <span>Pilih Foto</span>
                                            <input
                                                type="file"
                                                accept="image/png, image/jpeg, image/jpg"
                                                class="hidden"
                                                @change="handleLogoChange"
                                            />
                                        </label>
                                        <p class="mt-2 text-xs text-[#7c8c9a]">
                                            Format .PNG atau .JPG, maks. 2MB.
                                        </p>
                                    </div>
                                </div>
                                <p v-if="form.errors.logo" class="text-sm text-destructive">{{ form.errors.logo }}</p>
                            </div>

                            <div class="space-y-2">
                                <Label for="receipt_footer">Pesan Footer Struk</Label>
                                <Input
                                    id="receipt_footer"
                                    v-model="form.receipt_footer"
                                    placeholder="Contoh: Terima kasih sudah berkunjung!"
                                />
                                <p v-if="form.errors.receipt_footer" class="text-sm text-destructive">{{ form.errors.receipt_footer }}</p>
                            </div>
                        </CardContent>
                        <CardFooter class="flex justify-end">
                            <Button type="submit" :disabled="form.processing">Simpan Perubahan</Button>
                        </CardFooter>
                    </form>
                </Card>
            </div>
    </SettingsLayout>
</template>
