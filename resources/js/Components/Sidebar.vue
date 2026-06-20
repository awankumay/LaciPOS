<script setup>
import { usePage, router, Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import {
    LayoutDashboard,
    ShoppingCart,
    Package,
    Tags,
    BarChart3,
    Settings,
    LogOut,
    Store,
} from 'lucide-vue-next';

const page = usePage();
const currentUrl = computed(() => page.url);
const user = computed(() => page.props.auth?.user);
const isOwner = computed(() => user.value?.role === 'owner');

const ownerMenuItems = [
    { href: '/dashboard', icon: LayoutDashboard, label: 'Dashboard' },
    { href: '/pos', icon: ShoppingCart, label: 'Kasir (POS)' },
    { href: '/products', icon: Package, label: 'Produk' },
    { href: '/discounts', icon: Tags, label: 'Diskon' },
    { href: '/categories', icon: Tags, label: 'Kategori' },
    { href: '/orders', icon: BarChart3, label: 'Riwayat Transaksi' },
    { href: '/reports', icon: BarChart3, label: 'Laporan' },
    { href: '/settings', icon: Settings, label: 'Pengaturan' },
];

const cashierMenuItems = [
    { href: '/pos', icon: ShoppingCart, label: 'Kasir (POS)' },
    { href: '/orders', icon: BarChart3, label: 'Riwayat Transaksi' },
];

const menuItems = computed(() => isOwner.value ? ownerMenuItems : cashierMenuItems);

const isActive = (href) => currentUrl.value.startsWith(href);

const logout = () => router.post('/logout');

const initials = computed(() => {
    const name = user.value?.name || '';
    return name.split(' ').map(w => w[0]).slice(0, 2).join('').toUpperCase();
});
</script>

<template>
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col bg-[#001e2b]">
        <!-- Logo / Brand -->
        <div class="flex h-16 items-center gap-2.5 px-6 border-b border-[#1c2d38]">
            <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-[#00ed64]">
                <Store class="h-4.5 w-4.5 text-[#001e2b]" />
            </div>
            <span class="text-base font-bold tracking-tight text-white">POS Desktop</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-5 space-y-0.5">
            <!-- Section label -->
            <p class="mb-2 px-3 text-[10px] font-semibold uppercase tracking-widest text-[#5c6c7a]">Menu</p>

            <Link
                v-for="item in menuItems"
                :key="item.href"
                :href="item.href"
                :class="[
                    'flex items-center gap-3 rounded-lg px-3 py-2.5 text-sm font-medium transition-all duration-150',
                    isActive(item.href)
                        ? 'bg-[#00ed64] text-[#001e2b]'
                        : 'text-[#a8b3bc] hover:bg-[#1c2d38] hover:text-white',
                ]"
            >
                <component
                    :is="item.icon"
                    :class="['h-4.5 w-4.5 shrink-0', isActive(item.href) ? 'text-[#001e2b]' : 'text-[#5c6c7a]']"
                />
                {{ item.label }}
            </Link>
        </nav>

        <!-- User info + Logout -->
        <div class="border-t border-[#1c2d38] p-4">
            <!-- User badge -->
            <div class="mb-3 flex items-center gap-3 rounded-xl bg-[#1c2d38] px-3 py-2.5">
                <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-[#00684a] text-xs font-bold text-[#00ed64]">
                    {{ initials }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="truncate text-sm font-semibold text-white">{{ user?.name }}</p>
                    <p class="text-[10px] font-medium uppercase tracking-wide text-[#5c6c7a]">{{ user?.role }}</p>
                </div>
            </div>

            <!-- Logout button -->
            <button
                @click="logout"
                class="flex w-full items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium text-[#5c6c7a] transition-colors hover:bg-[#1c2d38] hover:text-red-400"
            >
                <LogOut class="h-4 w-4" />
                Keluar
            </button>
        </div>
    </aside>
</template>
