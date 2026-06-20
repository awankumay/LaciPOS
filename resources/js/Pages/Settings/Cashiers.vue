<script setup>
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/Components/ui/table';
import { Badge } from '@/Components/ui/badge';
import Modal from '@/Components/Modal.vue';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Plus, Pencil, Check, ChevronsUpDown } from 'lucide-vue-next';

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
const openActiveBox = ref(false);
const editForm = useForm({
    name: '',
    email: '',
    password: '',
    is_active: true,
});
const editingCashierId = ref(null);

const startEdit = (cashier) => {
    editingCashierId.value = cashier.id;
    editForm.name = cashier.name;
    editForm.email = cashier.email;
    editForm.password = '';
    editForm.is_active = Boolean(cashier.is_active);
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

// Format Date
const formatDate = (dateString) => {
    const date = new Date(dateString);
    return new Intl.DateTimeFormat('id-ID', {
        year: 'numeric', month: 'short', day: 'numeric'
    }).format(date);
};
</script>

<template>
    <SettingsLayout title="Manajemen Kasir">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold text-gray-900">Manajemen Kasir</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola akun kasir untuk aplikasi ini.</p>
                </div>
                <button @click="showAddDialog = true" class="px-6 py-2 bg-[#00ed64] text-[#001e2b] rounded-lg hover:bg-[#00b545] transition-colors font-medium text-sm flex items-center shrink-0">
                    <Plus class="mr-2 h-4 w-4" />
                    Tambah Kasir
                </button>
            </div>

            <div class="p-0">
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
                                    <button @click="startEdit(cashier)" class="p-2 text-gray-500 hover:bg-gray-100 rounded-md transition-colors">
                                        <Pencil class="h-4 w-4" />
                                    </button>
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
        </div>

        <!-- Add Dialog -->
        <Modal
            :show="showAddDialog"
            @update:show="showAddDialog = $event"
            title="Tambah Akun Kasir"
            description="Buat akun baru untuk kasir Anda. Kasir akan menggunakan email dan password ini untuk login."
        >
            <form id="add-cashier-form" @submit.prevent="submitAdd" class="space-y-4 py-2">
                <div class="space-y-2">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input id="name" v-model="addForm.name" placeholder="Nama Kasir" autofocus class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" />
                    <p v-if="addForm.errors.name" class="text-sm text-red-500">{{ addForm.errors.name }}</p>
                </div>
                <div class="space-y-2">
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="email" type="email" v-model="addForm.email" placeholder="kasir@contoh.com" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" />
                    <p v-if="addForm.errors.email" class="text-sm text-red-500">{{ addForm.errors.email }}</p>
                </div>
                <div class="space-y-2">
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input id="password" type="password" v-model="addForm.password" placeholder="Minimal 8 karakter" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" />
                    <p v-if="addForm.errors.password" class="text-sm text-red-500">{{ addForm.errors.password }}</p>
                </div>
            </form>
            <template #footer>
                <div class="flex items-center gap-3 w-full justify-end">
                    <button type="button" @click="showAddDialog = false" class="px-6 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center">Batal</button>
                    <button type="submit" form="add-cashier-form" :disabled="addForm.processing" class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm flex items-center disabled:opacity-50">Simpan</button>
                </div>
            </template>
        </Modal>

        <!-- Edit Dialog -->
        <Modal
            :show="showEditDialog"
            @update:show="showEditDialog = $event"
            title="Edit Akun Kasir"
            description="Ubah informasi kasir. Kosongkan field password jika tidak ingin mengubahnya."
        >
            <form id="edit-cashier-form" @submit.prevent="submitEdit" class="space-y-4 py-2">
                <div class="space-y-2">
                    <label for="edit_name" class="block text-sm font-medium text-gray-700 mb-1">Nama Lengkap</label>
                    <input id="edit_name" v-model="editForm.name" placeholder="Nama Kasir" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" />
                    <p v-if="editForm.errors.name" class="text-sm text-red-500">{{ editForm.errors.name }}</p>
                </div>
                <div class="space-y-2">
                    <label for="edit_email" class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input id="edit_email" type="email" v-model="editForm.email" placeholder="kasir@contoh.com" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" />
                    <p v-if="editForm.errors.email" class="text-sm text-red-500">{{ editForm.errors.email }}</p>
                </div>
                <div class="space-y-2">
                    <label for="edit_password" class="block text-sm font-medium text-gray-700 mb-1">Password Baru (Opsional)</label>
                    <input id="edit_password" type="password" v-model="editForm.password" placeholder="Biarkan kosong jika tidak diubah" class="h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10 px-4" />
                    <p v-if="editForm.errors.password" class="text-sm text-red-500">{{ editForm.errors.password }}</p>
                </div>
                <div class="space-y-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Status Akun</label>
                    <Popover v-model:open="openActiveBox">
                        <PopoverTrigger as-child>
                            <button
                                type="button"
                                role="combobox"
                                :aria-expanded="openActiveBox"
                                class="flex items-center justify-between h-10 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                            >
                                <span class="truncate">{{ editForm.is_active ? 'Aktif' : 'Nonaktif' }}</span>
                                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                            </button>
                        </PopoverTrigger>
                        <PopoverContent class="w-full p-0 bg-white" align="start">
                            <Command>
                                <CommandList>
                                    <CommandGroup>
                                        <CommandItem
                                            value="Aktif"
                                            @select="() => {
                                                editForm.is_active = true;
                                                openActiveBox = false;
                                            }"
                                            class="text-sm cursor-pointer"
                                        >
                                            Aktif
                                            <Check
                                                :class="['ml-auto h-4 w-4', editForm.is_active === true ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                            />
                                        </CommandItem>
                                        <CommandItem
                                            value="Nonaktif"
                                            @select="() => {
                                                editForm.is_active = false;
                                                openActiveBox = false;
                                            }"
                                            class="text-sm cursor-pointer"
                                        >
                                            Nonaktif
                                            <Check
                                                :class="['ml-auto h-4 w-4', editForm.is_active === false ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                            />
                                        </CommandItem>
                                    </CommandGroup>
                                </CommandList>
                            </Command>
                        </PopoverContent>
                    </Popover>
                    <p v-if="editForm.errors.is_active" class="text-sm text-red-500">{{ editForm.errors.is_active }}</p>
                </div>
            </form>
            <template #footer>
                <div class="flex items-center gap-3 w-full justify-end">
                    <button type="button" @click="showEditDialog = false" class="px-6 py-2 bg-white text-gray-700 border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors font-medium text-sm flex items-center">Batal</button>
                    <button type="submit" form="edit-cashier-form" :disabled="editForm.processing" class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm flex items-center disabled:opacity-50">Simpan Perubahan</button>
                </div>
            </template>
        </Modal>
    </SettingsLayout>
</template>
