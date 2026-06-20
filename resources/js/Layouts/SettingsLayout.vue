<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: String,
});

const page = usePage();

const navItems = [
    { name: 'Informasi Toko', url: '/settings/store' },
    { name: 'Printer', url: '/settings/printer' },
    { name: 'Manajemen Kasir', url: '/settings/cashiers' },
    { name: 'Pajak & Layanan', url: '/settings/taxes' },
    { name: 'Stok', url: '/settings/inventory' },
    { name: 'Backup Database', url: '/settings/backup' },
];
</script>

<template>
    <AppLayout :title="title">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Pengaturan</h1>
                    <p class="text-xs text-[#7c8c9a]">Kelola konfigurasi sistem dan preferensi aplikasi</p>
                </div>
            </div>
        </template>

        <div class="flex flex-col md:flex-row gap-8 items-start max-w-6xl mx-auto w-full">
            <!-- Sidebar Settings -->
            <nav class="w-full md:w-64 flex-shrink-0 flex flex-col space-y-1">
                <Link
                    v-for="item in navItems"
                    :key="item.name"
                    :href="item.url"
                    class="block px-3 py-2 text-sm font-medium rounded-md transition-colors"
                    :class="[
                        page.url.startsWith(item.url)
                            ? 'bg-[#001e2b] text-[#00ed64]' // brand-teal-deep bg and brand-green text
                            : 'text-[#5c6c7a] hover:bg-[#eceff1] hover:text-[#1c2d38]' // steel text, hairline-soft bg, charcoal text
                    ]"
                >
                    {{ item.name }}
                </Link>
            </nav>

            <!-- Main Settings Content -->
            <div class="flex-1 min-w-0 w-full space-y-6">
                <slot />
            </div>
        </div>
    </AppLayout>
</template>
