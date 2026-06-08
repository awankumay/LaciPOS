<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import SidebarNavItem from '@/Components/SidebarNavItem.vue';
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

// Menu navigasi untuk owner
const ownerMenuItems = [
    { href: '/dashboard', icon: LayoutDashboard, label: 'Dashboard' },
    { href: '/pos', icon: ShoppingCart, label: 'Kasir (POS)' },
    { href: '/products', icon: Package, label: 'Produk' },
    { href: '/categories', icon: Tags, label: 'Kategori' },
    { href: '/reports', icon: BarChart3, label: 'Laporan' },
    { href: '/settings', icon: Settings, label: 'Pengaturan' },
];

// Menu navigasi untuk cashier (terbatas)
const cashierMenuItems = [
    { href: '/pos', icon: ShoppingCart, label: 'Kasir (POS)' },
    { href: '/orders', icon: BarChart3, label: 'Riwayat Transaksi' },
];

const menuItems = computed(() => isOwner.value ? ownerMenuItems : cashierMenuItems);

const isActive = (href) => {
    return currentUrl.value.startsWith(href);
};
</script>

<template>
    <aside class="fixed inset-y-0 left-0 z-50 flex w-64 flex-col border-r border-slate-200 bg-white">
        <!-- Logo / Brand -->
        <div class="flex h-16 items-center gap-2 border-b border-slate-200 px-6">
            <Store class="h-7 w-7 text-slate-800" />
            <span class="text-lg font-semibold text-slate-800">POS Desktop</span>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
            <SidebarNavItem
                v-for="item in menuItems"
                :key="item.href"
                :href="item.href"
                :icon="item.icon"
                :label="item.label"
                :active="isActive(item.href)"
            />
        </nav>

        <!-- User Info + Logout -->
        <div class="border-t border-slate-200 p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-slate-100 text-sm font-medium text-slate-700">
                    {{ user?.name?.charAt(0)?.toUpperCase() }}
                </div>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-slate-900 truncate">{{ user?.name }}</p>
                    <p class="text-xs text-slate-500 capitalize">{{ user?.role }}</p>
                </div>
            </div>
            <SidebarNavItem
                href="/logout"
                :icon="LogOut"
                label="Keluar"
                :active="false"
            />
        </div>
    </aside>
</template>
