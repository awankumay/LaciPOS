<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    ShoppingCart,
    Package,
    TrendingUp,
    Users,
    ArrowUpRight,
    Clock,
    Sparkles,
    Wallet,
    AlertTriangle,
} from 'lucide-vue-next';

import StatCard from '@/Components/StatCard.vue';
import SalesChart from '@/Components/SalesChart.vue';

const props = defineProps({
    stats: Object,
    restockProducts: {
        type: Array,
        default: () => [],
    },
    salesData: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const user = computed(() => page.props.auth?.user);
const greeting = computed(() => {
    const hour = new Date().getHours();
    if (hour < 12) return 'Selamat pagi';
    if (hour < 17) return 'Selamat siang';
    return 'Selamat malam';
});

const formatRupiah = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(value || 0);
};

const dashboardStats = computed(() => [
    {
        title: 'Pendapatan Hari Ini',
        value: formatRupiah(props.stats?.totalRevenue),
        icon: TrendingUp,
    },
    {
        title: 'Laba Kotor Hari Ini',
        value: formatRupiah(props.stats?.grossProfit),
        icon: Wallet,
    },
    {
        title: 'Transaksi Hari Ini',
        value: props.stats?.totalTransactions || 0,
        icon: ShoppingCart,
    },
    {
        title: 'Kasir Aktif',
        value: props.stats?.activeCashiers || 0,
        icon: Users,
    },
]);

// Quick actions
const quickActions = [
    { label: 'Buka Kasir', href: '/pos', icon: ShoppingCart, color: 'bg-[#00ed64] text-[#001e2b] hover:bg-[#00b545]' },
    { label: 'Tambah Produk', href: '/products/create', icon: Package, color: 'bg-[#001e2b] text-white hover:bg-[#1c2d38]' },
    { label: 'Lihat Laporan', href: '/reports', icon: TrendingUp, color: 'bg-white text-[#001e2b] border border-[#e1e5e8] hover:border-[#001e2b]' },
];
</script>

<template>
    <AppLayout title="Dashboard">
        <template #header>
            <div class="flex items-center justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Dashboard</h1>
                </div>
                <div class="flex items-center gap-2 text-xs text-[#7c8c9a]">
                    <Clock class="h-3.5 w-3.5" />
                    {{ new Date().toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' }) }}
                </div>
            </div>
        </template>

        <div class="space-y-8">
            <!-- Greeting hero band -->
            <div class="rounded-2xl bg-[#001e2b] px-8 py-7 flex items-center justify-between overflow-hidden relative">
                <!-- Background decoration -->
                <div class="absolute right-0 top-0 h-full w-64 opacity-5">
                    <div class="absolute top-4 right-8 h-32 w-32 rounded-full bg-[#00ed64]" />
                    <div class="absolute bottom-4 right-24 h-20 w-20 rounded-full bg-[#00ed64]" />
                </div>

                <div>
                    <p class="text-xs font-semibold uppercase tracking-widest text-[#00ed64] mb-1">
                        {{ greeting }},
                    </p>
                    <h2 class="text-2xl font-bold text-white leading-snug">
                        {{ user?.name?.split(' ')[0] || 'Pengguna' }} 👋
                    </h2>
                    <p class="mt-1.5 text-sm text-[#a8b3bc]">
                        Selamat datang di POS Desktop. Semua yang Anda butuhkan ada di sini.
                    </p>
                </div>

                <div class="shrink-0 hidden sm:flex h-16 w-16 items-center justify-center rounded-2xl bg-[#00ed64]">
                    <Sparkles class="h-8 w-8 text-[#001e2b]" />
                </div>
            </div>

            <!-- Stat Cards -->
            <div>
                <h3 class="mb-4 text-xs font-semibold uppercase tracking-widest text-[#7c8c9a]">Ringkasan Hari Ini</h3>
                <div class="grid grid-cols-2 gap-4 xl:grid-cols-4">
                    <StatCard
                        v-for="stat in dashboardStats"
                        :key="stat.title"
                        :title="stat.title"
                        :value="stat.value"
                        :icon="stat.icon"
                    />
                </div>
            </div>

            <!-- Quick Actions & Restock Alert -->
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="lg:col-span-1 flex flex-col gap-6">
                    <!-- Quick Actions -->
                    <div>
                        <h3 class="mb-4 text-xs font-semibold uppercase tracking-widest text-[#7c8c9a]">Aksi Cepat</h3>
                        <div class="space-y-3">
                            <a
                                v-for="action in quickActions"
                                :key="action.label"
                                :href="action.href"
                                :class="[
                                    'flex items-center justify-between rounded-xl px-4 py-3.5 text-sm font-semibold transition-all duration-150',
                                    action.color,
                                ]"
                            >
                                <div class="flex items-center gap-3">
                                    <component :is="action.icon" class="h-4 w-4" />
                                    {{ action.label }}
                                </div>
                                <ArrowUpRight class="h-4 w-4 opacity-60" />
                            </a>
                        </div>
                    </div>

                    <!-- Restock Alert -->
                    <div>
                        <div class="mb-4 flex items-center justify-between">
                            <h3 class="text-xs font-semibold uppercase tracking-widest text-[#7c8c9a]">Perlu Restock</h3>
                            <span v-if="restockProducts.length > 0" class="inline-flex items-center rounded-full bg-[#fde8e8] px-2 py-0.5 text-[10px] font-bold text-[#9b1c1c]">
                                {{ restockProducts.length }} Produk
                            </span>
                        </div>
                        <div class="rounded-2xl border border-[#e1e5e8] bg-white overflow-hidden shadow-[0_1px_2px_rgba(0,30,43,0.04)]">
                            <ul v-if="restockProducts.length > 0" class="divide-y divide-[#e1e5e8]">
                                <li v-for="product in restockProducts" :key="product.id" class="px-4 py-3 hover:bg-slate-50 transition-colors">
                                    <div class="flex items-center justify-between">
                                        <div class="min-w-0 pr-3">
                                            <p class="text-sm font-medium text-[#001e2b] truncate" :title="product.name">{{ product.name }}</p>
                                            <p class="text-xs text-[#5c6c7a] truncate">{{ product.category?.name || 'Tanpa Kategori' }}</p>
                                        </div>
                                        <div class="text-right shrink-0">
                                            <p class="text-sm font-bold text-[#e02424]">{{ product.stock }}</p>
                                            <p class="text-[10px] text-[#7c8c9a]">Min: {{ product.min_stock_alert }}</p>
                                        </div>
                                    </div>
                                </li>
                            </ul>
                            <div v-else class="flex flex-col items-center justify-center py-8 text-center px-4">
                                <div class="mb-3 flex h-10 w-10 items-center justify-center rounded-full bg-[#def7ec]">
                                    <Package class="h-5 w-5 text-[#03543f]" />
                                </div>
                                <p class="text-sm font-medium text-[#001e2b]">Stok Aman</p>
                                <p class="mt-1 text-xs text-[#7c8c9a]">Tidak ada produk yang kurang.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Activity placeholder -->
                <div class="lg:col-span-2">
                    <h3 class="mb-4 text-xs font-semibold uppercase tracking-widest text-[#7c8c9a]">Transaksi Terbaru</h3>
                    <div class="rounded-2xl border border-[#e1e5e8] bg-white">
                        <!-- Empty state -->
                        <div class="flex flex-col items-center justify-center py-16 text-center">
                            <div class="mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-[#f4f7f6]">
                                <ShoppingCart class="h-7 w-7 text-[#a8b3bc]" />
                            </div>
                            <p class="text-sm font-semibold text-[#3d4f5b]">Belum ada transaksi</p>
                            <p class="mt-1 text-xs text-[#7c8c9a]">Transaksi akan muncul di sini setelah Kasir digunakan.</p>
                            <a
                                href="/pos"
                                class="mt-5 inline-flex items-center gap-2 rounded-full bg-[#00ed64] px-5 py-2 text-xs font-bold text-[#001e2b] transition-colors hover:bg-[#00b545]"
                            >
                                <ShoppingCart class="h-3.5 w-3.5" />
                                Buka Kasir
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sales Chart -->
            <div>
                <div class="mb-4 flex items-center justify-between">
                    <h3 class="text-xs font-semibold uppercase tracking-widest text-[#7c8c9a]">Grafik Pendapatan 7 Hari Terakhir</h3>
                </div>
                <div class="rounded-2xl border border-[#e1e5e8] bg-white px-6 pb-6 shadow-[0_1px_2px_rgba(0,30,43,0.04)] overflow-x-auto">
                    <div class="min-w-[500px]">
                        <SalesChart :data="salesData" />
                    </div>
                </div>
            </div>

            <!-- Coming Soon notice -->
            <div class="rounded-2xl border border-dashed border-[#c1ccd6] bg-transparent px-6 py-5">
                <div class="flex items-start gap-4">
                    <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-[#e3fcef]">
                        <Sparkles class="h-4 w-4 text-[#00684a]" />
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-[#001e2b]">Dashboard sedang dalam pengembangan</p>
                        <p class="mt-0.5 text-xs text-[#7c8c9a]">
                            Statistik penjualan, grafik pendapatan, dan laporan lengkap akan tersedia di task selanjutnya.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
