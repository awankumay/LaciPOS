<script setup>
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Download, Database, Upload, AlertTriangle, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import Modal from '@/Components/Modal.vue';

const props = defineProps({
    backups: { type: Array, default: () => [] },
});

const form = useForm({});
const restoreForm = useForm({
    backup_file: null,
});

const isRestoreModalOpen = ref(false);
const fileInputRef = ref(null);

const currentPage = ref(1);
const itemsPerPage = 5;

const totalPages = computed(() => Math.ceil(props.backups.length / itemsPerPage));

const paginatedBackups = computed(() => {
    const start = (currentPage.value - 1) * itemsPerPage;
    const end = start + itemsPerPage;
    return props.backups.slice(start, end);
});

const prevPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const nextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

const createBackup = () => {
    form.post('/settings/backup', {
        preserveScroll: true,
    });
};

const handleFileChange = (e) => {
    restoreForm.backup_file = e.target.files[0];
};

const triggerFileInput = () => {
    fileInputRef.value.click();
};

const processRestore = () => {
    restoreForm.post('/settings/restore', {
        preserveScroll: true,
        onSuccess: () => {
            isRestoreModalOpen.value = false;
            restoreForm.reset();
            router.visit('/dashboard');
        },
    });
};
</script>

<template>
    <SettingsLayout title="Backup & Restore">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6 mt-4">
            <div class="p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex-1 pr-0 md:pr-4">
                    <h2 class="text-lg font-semibold text-gray-900">Backup Otomatis/Manual</h2>
                    <p class="text-sm text-gray-500 mt-1">Buat backup database lokal Anda. Sangat disarankan untuk melakukan backup secara rutin untuk menghindari kehilangan data.</p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto shrink-0">
                    <button @click="createBackup" :disabled="form.processing" class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm flex items-center justify-center disabled:opacity-50 flex-1 md:flex-none">
                        <Database class="mr-2 h-4 w-4" />
                        {{ form.processing ? 'Membuat Backup...' : 'Buat Backup Sekarang' }}
                    </button>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100">
                <h2 class="text-lg font-semibold text-gray-900">Riwayat Backup</h2>
                <p class="text-sm text-gray-500 mt-1">Daftar backup yang telah dibuat sebelumnya.</p>
            </div>
            <div class="p-0">
                <Table>
                    <TableHeader>
                        <TableRow>
                            <TableHead>Nama File</TableHead>
                            <TableHead>Ukuran</TableHead>
                            <TableHead>Tanggal Dibuat</TableHead>
                            <TableHead class="text-right">Aksi</TableHead>
                        </TableRow>
                    </TableHeader>
                    <TableBody>
                        <TableRow v-for="backup in paginatedBackups" :key="backup.filename">
                            <TableCell class="font-medium">{{ backup.filename }}</TableCell>
                            <TableCell>{{ backup.size }} KB</TableCell>
                            <TableCell>{{ backup.created_at }}</TableCell>
                            <TableCell class="text-right">
                                <a :href="`/settings/backup/${backup.filename}/download`" target="_blank">
                                    <button class="px-4 py-1.5 bg-[#00ed64] text-[#001e2b] rounded-md hover:bg-[#00b545] transition-colors font-medium text-xs inline-flex items-center">
                                        <Download class="mr-1.5 h-3.5 w-3.5" />
                                        Download
                                    </button>
                                </a>
                            </TableCell>
                        </TableRow>
                        <TableRow v-if="backups.length === 0">
                            <TableCell colspan="4" class="text-center text-slate-400 py-8">
                                Belum ada backup yang dibuat.
                            </TableCell>
                        </TableRow>
                    </TableBody>
                </Table>
                
                <div v-if="totalPages > 1" class="p-4 border-t border-gray-100 flex items-center justify-between">
                    <p class="text-sm text-gray-500">
                        Menampilkan {{ (currentPage - 1) * itemsPerPage + 1 }} hingga {{ Math.min(currentPage * itemsPerPage, backups.length) }} dari {{ backups.length }} data
                    </p>
                    <div class="flex items-center gap-2">
                        <button 
                            @click="prevPage" 
                            :disabled="currentPage === 1"
                            :class="[
                                'p-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
                                currentPage === 1 
                                    ? 'border border-gray-200 text-gray-400 bg-white' 
                                    : 'bg-[#001e2b] text-[#00ed62] hover:bg-gray-800 border border-transparent'
                            ]"
                        >
                            <ChevronLeft class="w-4 h-4" />
                        </button>
                        <button 
                            @click="nextPage" 
                            :disabled="currentPage === totalPages"
                            :class="[
                                'p-2 rounded-lg transition-colors disabled:opacity-50 disabled:cursor-not-allowed',
                                currentPage === totalPages 
                                    ? 'border border-gray-200 text-gray-400 bg-white' 
                                    : 'bg-[#001e2b] text-[#00ed62] hover:bg-gray-800 border border-transparent'
                            ]"
                        >
                            <ChevronRight class="w-4 h-4" />
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6 mt-6">
            <div class="p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div class="flex-1 pr-0 md:pr-4">
                    <h2 class="text-lg font-semibold text-gray-900">Restore Database</h2>
                    <p class="text-sm text-gray-500 mt-1">Pulihkan seluruh data aplikasi (termasuk produk, transaksi, dan pengaturan) menggunakan file backup (.sqlite) yang pernah Anda unduh sebelumnya.</p>
                </div>
                <div class="flex items-center gap-3 w-full md:w-auto shrink-0">
                    <button @click="isRestoreModalOpen = true" class="px-6 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center justify-center flex-1 md:flex-none">
                        <Upload class="mr-2 h-4 w-4" />
                        Restore Database
                    </button>
                </div>
            </div>
        </div>

        <Modal
            :show="isRestoreModalOpen"
            @update:show="isRestoreModalOpen = $event"
            title="Restore Database"
            description="Peringatan: Seluruh data Anda saat ini akan ditimpa dengan data dari file backup."
        >
            <form id="restore-form" @submit.prevent="processRestore" class="space-y-4 py-2">
                <div class="bg-red-50 border border-red-200 rounded-lg p-4 flex items-start gap-3">
                    <AlertTriangle class="h-5 w-5 text-red-600 shrink-0 mt-0.5" />
                    <div>
                        <h4 class="text-sm font-semibold text-red-800">Tindakan Berbahaya</h4>
                        <p class="text-xs text-red-700 mt-1">
                            Memulihkan database akan **mengganti seluruh data yang ada** (produk, kategori, transaksi, dan pengaturan) dengan data dari file backup yang Anda pilih. Tindakan ini tidak dapat dibatalkan.
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Pilih File Backup (.sqlite)</label>
                    <div 
                        @click="triggerFileInput" 
                        class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center cursor-pointer hover:bg-gray-50 hover:border-[#00684a] transition-colors"
                    >
                        <input 
                            type="file" 
                            ref="fileInputRef" 
                            class="hidden" 
                            accept=".sqlite" 
                            @change="handleFileChange" 
                        />
                        <div v-if="!restoreForm.backup_file" class="flex flex-col items-center justify-center">
                            <Upload class="h-8 w-8 text-gray-400 mb-3" />
                            <p class="text-sm font-medium text-gray-900">Klik untuk memilih file</p>
                            <p class="text-xs text-gray-500 mt-1">Hanya file berekstensi .sqlite yang didukung</p>
                        </div>
                        <div v-else class="flex flex-col items-center justify-center">
                            <Database class="h-8 w-8 text-[#00684a] mb-3" />
                            <p class="text-sm font-semibold text-[#001e2b]">{{ restoreForm.backup_file.name }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ (restoreForm.backup_file.size / 1024).toFixed(2) }} KB</p>
                        </div>
                    </div>
                <div v-if="restoreForm.errors.backup_file" class="text-red-500 text-xs mt-1">{{ restoreForm.errors.backup_file }}</div>
                </div>
            </form>
            <template #footer>
                <div class="flex items-center gap-3 w-full justify-end">
                    <button type="button" @click="isRestoreModalOpen = false" class="px-6 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center">Batal</button>
                    <button type="submit" form="restore-form" :disabled="!restoreForm.backup_file || restoreForm.processing" class="px-6 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors font-medium text-sm flex items-center disabled:opacity-50">
                        {{ restoreForm.processing ? 'Memulihkan...' : 'Ya, Timpa Data Sekarang' }}
                    </button>
                </div>
            </template>
        </Modal>
    </SettingsLayout>
</template>
