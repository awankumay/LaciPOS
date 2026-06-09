<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';
import { Switch } from '@/Components/ui/switch';
import { Plus, Pencil } from 'lucide-vue-next';

const props = defineProps({
    cashiers: { type: Array, default: () => [] },
});

// Add Cashier Form
const showAddDialog = ref(false);
const addForm = useForm({
    name: '',
    email: '',
    password: '',
});

const submitAdd = () => {
    addForm.post('/settings/cashiers', {
        onSuccess: () => {
            showAddDialog.value = false;
            addForm.reset();
        },
    });
};

// Edit Cashier Form
const showEditDialog = ref(false);
const editForm = useForm({
    name: '',
    email: '',
    password: '',
});
const editingCashierId = ref(null);

const startEdit = (cashier) => {
    editingCashierId.value = cashier.id;
    editForm.name = cashier.name;
    editForm.email = cashier.email;
    editForm.password = '';
    showEditDialog.value = true;
};

const submitEdit = () => {
    editForm.put(`/settings/cashiers/${editingCashierId.value}`, {
        onSuccess: () => {
            showEditDialog.value = false;
            editForm.reset();
        },
    });
};

// Toggle Active
const toggleActive = (cashier) => {
    router.patch(`/settings/cashiers/${cashier.id}/toggle`, {}, { preserveScroll: true });
};

// Format Date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric'
    }).format(date);
};
</script>

<template>
    <AppLayout>
        <Head title="Manajemen Kasir" />

        <template #header>
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-semibold text-slate-900">Manajemen Kasir</h1>
                <Button @click="showAddDialog = true">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kasir
                </Button>
            </div>
        </template>

        <div class="rounded-lg border border-slate-200 bg-white">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Nama</TableHead>
                        <TableHead>Email</TableHead>
                        <TableHead>Status</TableHead>
                        <TableHead>Tanggal Dibuat</TableHead>
                        <TableHead class="text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="cashier in cashiers" :key="cashier.id">
                        <TableCell class="font-medium">{{ cashier.name }}</TableCell>
                        <TableCell>{{ cashier.email }}</TableCell>
                        <TableCell>
                            <Badge :variant="cashier.is_active ? 'default' : 'secondary'" :class="cashier.is_active ? 'bg-green-100 text-green-700 hover:bg-green-100' : ''">
                                {{ cashier.is_active ? 'Aktif' : 'Nonaktif' }}
                            </Badge>
                        </TableCell>
                        <TableCell>{{ formatDate(cashier.created_at) }}</TableCell>
                        <TableCell class="text-right">
                            <div class="flex items-center justify-end gap-2">
                                <Switch
                                    :checked="Boolean(cashier.is_active)"
                                    @update:checked="toggleActive(cashier)"
                                    title="Toggle Status Aktif"
                                />
                                <Button variant="ghost" size="sm" @click="startEdit(cashier)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="cashiers.length === 0">
                        <TableCell colspan="5" class="text-center text-slate-400 py-8">
                            Belum ada akun kasir.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Add Dialog -->
        <Dialog v-model:open="showAddDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Tambah Akun Kasir</DialogTitle>
                    <DialogDescription>
                        Buat akun baru untuk kasir Anda. Kasir akan menggunakan email dan password ini untuk login.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitAdd" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="name">Nama Lengkap</Label>
                        <Input id="name" v-model="addForm.name" placeholder="Nama Kasir" autofocus />
                        <p v-if="addForm.errors.name" class="text-sm text-red-500">{{ addForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="email">Email</Label>
                        <Input id="email" type="email" v-model="addForm.email" placeholder="kasir@contoh.com" />
                        <p v-if="addForm.errors.email" class="text-sm text-red-500">{{ addForm.errors.email }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="password">Password</Label>
                        <Input id="password" type="password" v-model="addForm.password" placeholder="Minimal 8 karakter" />
                        <p v-if="addForm.errors.password" class="text-sm text-red-500">{{ addForm.errors.password }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showAddDialog = false">Batal</Button>
                        <Button type="submit" :disabled="addForm.processing">Simpan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>

        <!-- Edit Dialog -->
        <Dialog v-model:open="showEditDialog">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>Edit Akun Kasir</DialogTitle>
                    <DialogDescription>
                        Ubah informasi kasir. Kosongkan field password jika tidak ingin mengubahnya.
                    </DialogDescription>
                </DialogHeader>
                <form @submit.prevent="submitEdit" class="space-y-4 py-4">
                    <div class="space-y-2">
                        <Label for="edit_name">Nama Lengkap</Label>
                        <Input id="edit_name" v-model="editForm.name" placeholder="Nama Kasir" />
                        <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_email">Email</Label>
                        <Input id="edit_email" type="email" v-model="editForm.email" placeholder="kasir@contoh.com" />
                        <p v-if="editForm.errors.email" class="text-sm text-red-500">{{ editForm.errors.email }}</p>
                    </div>
                    <div class="space-y-2">
                        <Label for="edit_password">Password Baru (Opsional)</Label>
                        <Input id="edit_password" type="password" v-model="editForm.password" placeholder="Biarkan kosong jika tidak diubah" />
                        <p v-if="editForm.errors.password" class="text-sm text-red-500">{{ editForm.errors.password }}</p>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" @click="showEditDialog = false">Batal</Button>
                        <Button type="submit" :disabled="editForm.processing">Simpan Perubahan</Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
