<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import { useForm, router } from '@inertiajs/vue3';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import Modal from '@/Components/Modal.vue';
import { Plus, Pencil, Trash2, Check, X } from 'lucide-vue-next';
import { ref } from 'vue';

const props = defineProps({
    categories: { type: Array, default: () => [] },
});

// Form untuk tambah kategori baru
const addForm = useForm({ name: '' });
const showAddForm = ref(false);

const submitAdd = () => {
    addForm.post('/categories', {
        onSuccess: () => {
            addForm.reset();
            showAddForm.value = false;
        },
    });
};

// Form untuk edit kategori
const editForm = useForm({ name: '' });
const editingId = ref(null);

const startEdit = (category) => {
    editingId.value = category.id;
    editForm.name = category.name;
};

const cancelEdit = () => {
    editingId.value = null;
    editForm.reset();
};

const submitEdit = (id) => {
    editForm.put(`/categories/${id}`, {
        onSuccess: () => {
            editingId.value = null;
        },
    });
};

// Delete
const showDeleteDialog = ref(false);
const deletingCategory = ref(null);

const confirmDelete = (category) => {
    deletingCategory.value = category;
    showDeleteDialog.value = true;
};

const executeDelete = () => {
    router.delete(`/categories/${deletingCategory.value.id}`, {
        onFinish: () => {
            showDeleteDialog.value = false;
            deletingCategory.value = null;
        },
    });
};
</script>

<template>
    <AppLayout title="Kategori">
        <template #header>
            <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between w-full">
                <div>
                    <h1 class="text-lg font-semibold text-[#001e2b]">Kategori</h1>
                    <p class="text-xs text-[#7c8c9a]">Kelola daftar kategori untuk produk Anda</p>
                </div>
            </div>
        </template>

        <div class="mb-6 flex justify-end">
            <Button @click="showAddForm = !showAddForm" size="sm" class="bg-[#001e2b] text-white hover:bg-[#1c2d38]">
                <Plus class="mr-2 h-4 w-4" />
                Tambah Kategori
            </Button>
        </div>

        <!-- Add Form -->
        <div v-if="showAddForm" class="mb-6 rounded-lg border border-slate-200 bg-white p-4">
            <form @submit.prevent="submitAdd" class="flex items-end gap-3">
                <div class="flex-1">
                    <label class="text-sm font-medium text-slate-700 mb-1 block">Nama Kategori</label>
                    <Input v-model="addForm.name" placeholder="Contoh: Minuman" autofocus class="rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" />
                    <p v-if="addForm.errors.name" class="text-sm text-red-500 mt-1">{{ addForm.errors.name }}</p>
                </div>
                <Button type="submit" :disabled="addForm.processing" size="sm">
                    <Check class="mr-1 h-4 w-4" /> Simpan
                </Button>
                <Button type="button" variant="outline" size="sm" @click="showAddForm = false; addForm.reset()">
                    <X class="mr-1 h-4 w-4" /> Batal
                </Button>
            </form>
        </div>

        <!-- Categories Table -->
        <div class="rounded-lg border border-slate-200 bg-white">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead>Nama Kategori</TableHead>
                        <TableHead class="w-[150px]">Jumlah Produk</TableHead>
                        <TableHead class="w-[120px] text-right">Aksi</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="category in categories" :key="category.id">
                        <TableCell>
                            <!-- Edit mode -->
                            <form v-if="editingId === category.id" @submit.prevent="submitEdit(category.id)" class="flex items-center gap-2">
                                <Input v-model="editForm.name" class="max-w-xs rounded-lg border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10" autofocus />
                                <Button type="submit" size="sm" variant="ghost" :disabled="editForm.processing">
                                    <Check class="h-4 w-4 text-green-600" />
                                </Button>
                                <Button type="button" size="sm" variant="ghost" @click="cancelEdit">
                                    <X class="h-4 w-4 text-slate-400" />
                                </Button>
                                <span v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</span>
                            </form>
                            <!-- View mode -->
                            <span v-else class="font-medium">{{ category.name }}</span>
                        </TableCell>
                        <TableCell>
                            <Badge variant="secondary">{{ category.products_count }} produk</Badge>
                        </TableCell>
                        <TableCell class="text-right">
                            <div v-if="editingId !== category.id" class="flex items-center justify-end gap-1">
                                <Button variant="ghost" size="sm" @click="startEdit(category)">
                                    <Pencil class="h-4 w-4" />
                                </Button>
                                <Button variant="ghost" size="sm" @click="confirmDelete(category)">
                                    <Trash2 class="h-4 w-4 text-red-500" />
                                </Button>
                            </div>
                        </TableCell>
                    </TableRow>
                    <TableRow v-if="categories.length === 0">
                        <TableCell colspan="3" class="text-center text-slate-400 py-8">
                            Belum ada kategori. Klik "Tambah Kategori" untuk memulai.
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <!-- Delete Confirmation Dialog -->
        <Modal
            :show="showDeleteDialog"
            @update:show="showDeleteDialog = $event"
            title="Hapus Kategori"
            :description="`Apakah Anda yakin ingin menghapus kategori \u0022${deletingCategory?.name}\u0022? Kategori yang masih memiliki produk tidak bisa dihapus.`"
        >
            <template #footer>
                <Button variant="outline" @click="showDeleteDialog = false">Batal</Button>
                <Button variant="destructive" @click="executeDelete" class="bg-red-600 text-white">Hapus</Button>
            </template>
        </Modal>
    </AppLayout>
</template>
