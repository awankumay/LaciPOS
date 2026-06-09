<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
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
    <AppLayout>
        <Head title="Backup Database" />

        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">Backup Database</h1>
            </div>
        </template>

        <div class="max-w-4xl mt-4 space-y-6">
            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Backup Otomatis/Manual</CardTitle>
                            <CardDescription>
                                Buat backup database lokal Anda. Sangat disarankan untuk melakukan backup secara rutin untuk menghindari kehilangan data.
                            </CardDescription>
                        </div>
                        <Button @click="createBackup" :disabled="form.processing">
                            <Database class="mr-2 h-4 w-4" />
                            {{ form.processing ? 'Membuat Backup...' : 'Buat Backup Sekarang' }}
                        </Button>
                    </div>
                </CardHeader>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Riwayat Backup</CardTitle>
                    <CardDescription>Daftar backup yang telah dibuat sebelumnya.</CardDescription>
                </CardHeader>
                <CardContent>
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
                                        <Button variant="outline" size="sm">
                                            <Download class="mr-2 h-4 w-4" />
                                            Download
                                        </Button>
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
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
