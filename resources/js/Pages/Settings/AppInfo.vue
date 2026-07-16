<script setup>
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { ExternalLink, Package, Github, Globe, User, Mail, Key, Database, AlertTriangle } from 'lucide-vue-next';

const props = defineProps({
    app_name: String,
    app_version: String,
    app_env: String,
    php_version: String,
    laravel_version: String,
    database: String,
    developer_name: String,
    developer_email: String,
    github_url: String,
    website_url: String,
    license: String,
});
</script>

<template>
    <SettingsLayout title="Info Aplikasi">
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 flex items-start gap-3 mt-4 mb-6">
            <AlertTriangle class="h-5 w-5 text-amber-600 shrink-0 mt-0.5" />
            <div>
                <h4 class="text-sm font-semibold text-amber-800">Versi Awal — Masih Aktif Dikembangkan</h4>
                <p class="text-xs text-amber-700 mt-1 leading-relaxed">
                    Aplikasi ini masih dalam tahap pengembangan awal. Kemungkinan masih terdapat bug atau perilaku yang tidak sesuai.
                    Jika Anda menemukan masalah, silakan laporkan melalui
                    <a :href="github_url + '/issues'" target="_blank" rel="noopener noreferrer" class="font-medium underline hover:text-amber-900">GitHub Issues</a>
                    atau hubungi pengembang.
                </p>
                <p class="text-xs text-amber-700 mt-2 leading-relaxed">
                    Mohon maaf atas ketidaknyamanannya. Terima kasih telah menggunakan aplikasi ini dan turut membantu pengembangan ke depannya.
                </p>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Info Aplikasi</h2>
                <p class="text-sm text-gray-500 mt-1">Informasi lengkap tentang aplikasi POS ini.</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl overflow-hidden">
                            <img src="/assets/logo/logo_desktop_app.png" alt="Logo" class="h-full w-full object-cover" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">Nama Aplikasi</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ app_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                            <Package class="h-5 w-5 text-[#00684a]" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">Versi Aplikasi</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ app_version }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl overflow-hidden">
                            <img src="/assets/settings/laravel-logo.png" alt="Laravel" class="h-full w-full object-cover" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">Laravel Version</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ laravel_version }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white overflow-hidden">
                            <img src="/assets/settings/php-logo.png" alt="PHP" class="h-9 w-9 object-contain" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">PHP Version</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ php_version }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f4f7f6]">
                            <Database class="h-5 w-5 text-[#003d4f]" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">Database</p>
                            <p class="text-sm text-gray-500 mt-0.5 capitalize">{{ database }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#f4f7f6]">
                            <Key class="h-5 w-5 text-[#003d4f]" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">Lisensi</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ license }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Pengembang</h2>
                <p class="text-sm text-gray-500 mt-1">Informasi pengembang aplikasi.</p>
            </div>

            <div class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                            <User class="h-5 w-5 text-[#00684a]" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">Pengembang</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ developer_name }}</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                            <Mail class="h-5 w-5 text-[#00684a]" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b]">Email</p>
                            <p class="text-sm text-gray-500 mt-0.5">{{ developer_email }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Tautan</h2>
                <p class="text-sm text-gray-500 mt-1">Tautan resmi terkait aplikasi ini.</p>
            </div>

            <div class="p-6 space-y-4">
                <a :href="github_url" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-gray-100 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#001e2b]">
                            <Github class="h-5 w-5 text-white" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b] group-hover:text-[#00684a] transition-colors">GitHub</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ github_url }}</p>
                        </div>
                    </div>
                    <ExternalLink class="h-4 w-4 text-gray-400 group-hover:text-[#00684a] transition-colors" />
                </a>

                <a :href="website_url" target="_blank" rel="noopener noreferrer"
                    class="flex items-center justify-between p-4 rounded-xl border border-gray-100 bg-gray-50 hover:bg-gray-100 transition-colors group">
                    <div class="flex items-center gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-[#00ed64]">
                            <Globe class="h-5 w-5 text-[#001e2b]" />
                        </div>
                        <div>
                            <p class="text-sm font-medium text-[#001e2b] group-hover:text-[#00684a] transition-colors">Website Resmi</p>
                            <p class="text-xs text-gray-500 mt-0.5">{{ website_url }}</p>
                        </div>
                    </div>
                    <ExternalLink class="h-4 w-4 text-gray-400 group-hover:text-[#00684a] transition-colors" />
                </a>
            </div>
        </div>
    </SettingsLayout>
</template>
