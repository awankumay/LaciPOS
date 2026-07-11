<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const currentUrl = computed(() => page.url.split('?')[0]);

const tabs = [
    { name: 'Pendapatan', href: '/reports/revenue' },
    { name: 'Laba / Rugi (Kotor)', href: '/reports/profit-loss' },
    { name: 'Produk Terlaris', href: '/reports/best-sellers' },
    { name: 'Pajak & SC', href: '/reports/tax-summary' },
];
</script>

<template>
    <div class="mb-6 flex space-x-1 rounded-xl bg-white p-1 border border-[#e1e5e8] shadow-sm max-w-fit">
        <Link
            v-for="tab in tabs"
            :key="tab.name"
            :href="tab.href"
            :class="[
                'rounded-lg px-4 py-2 text-sm font-semibold transition-all',
                currentUrl.startsWith(tab.href)
                    ? 'bg-[#001e2b] text-white shadow'
                    : 'text-[#5c6c7a] hover:bg-slate-50 hover:text-[#001e2b]'
            ]"
        >
            {{ tab.name }}
        </Link>
    </div>
</template>
