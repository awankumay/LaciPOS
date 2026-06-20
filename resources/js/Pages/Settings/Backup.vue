<script setup>
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Download, Database } from 'lucide-vue-next';

const props = defineProps({
    backups: { type: Array, default: () => [] },
});

const form = useForm({});

const createBackup = () => {
    form.post('/settings/backup', {
        preserveScroll: true,
    });
};
</script>

<template>
    <SettingsLayout title="Backup Database">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-6 mt-4">
            <div class="p-6 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Backup Otomatis/Manual</h2>
                    <p class="text-sm text-gray-500 mt-1">Buat backup database lokal Anda. Sangat disarankan untuk melakukan backup secara rutin untuk menghindari kehilangan data.</p>
                </div>
                <button @click="createBackup" :disabled="form.processing" class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm flex items-center disabled:opacity-50 shrink-0">
                    <Database class="mr-2 h-4 w-4" />
                    {{ form.processing ? 'Membuat Backup...' : 'Buat Backup Sekarang' }}
                </button>
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
                        <TableRow v-for="backup in backups" :key="backup.filename">
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
            </div>
        </div>
    </SettingsLayout>
</template>
