<script setup>
import SettingsLayout from '@/Layouts/SettingsLayout.vue';
import { useForm } from '@inertiajs/vue3';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Copy, Check, Eye, EyeOff, AlertTriangle, Download, X } from 'lucide-vue-next';
import { ref, watch } from 'vue';
import { useToast } from '@/composables/useToast';

const toast = useToast();
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';

const props = defineProps({
    security_question: { type: String, default: '' },
    hasSecuritySetup: { type: Boolean, default: false },
    recovery_codes_count: { type: Number, default: 0 },
    new_recovery_codes: { type: Array, default: null },
});

const showCodesModal = ref(false);
const newCodes = ref(null);
const showPassword = ref(false);
const showNewPassword = ref(false);
const showConfirmPassword = ref(false);
const copied = ref(false);

const securityQuestions = [
    'Apa nama hewan peliharaan pertama Anda?',
    'Apa nama kota tempat Anda lahir?',
    'Apa nama sekolah dasar Anda?',
    'Siapa nama tokoh idola Anda?',
    'Apa merek mobil pertama Anda?',
    'Apa nama makanan favorit Anda?',
    'Apa judul buku favorit Anda?',
    'Apa nama jalan tempat tinggal orang tua Anda?',
];

const securityForm = useForm({
    security_question: props.security_question,
    security_answer: '',
    current_password: '',
});

const passwordForm = useForm({
    current_password: '',
    new_password: '',
    new_password_confirmation: '',
});

const codesForm = useForm({
    current_password: '',
});

const submitSecurity = () => {
    securityForm.put('/settings/account/security', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            securityForm.reset('security_answer', 'current_password');
        },
    });
};

const submitPassword = () => {
    passwordForm.put('/settings/account/password', {
        preserveState: true,
        preserveScroll: true,
        onSuccess: () => {
            passwordForm.reset();
        },
    });
};

watch([() => securityForm.errors, () => passwordForm.errors, () => codesForm.errors], ([secErrors, pwdErrors, codeErrors]) => {
    const allErrors = { ...secErrors, ...pwdErrors, ...codeErrors };
    const firstError = Object.values(allErrors).find(Boolean);
    if (firstError) toast.error(firstError);
}, { deep: true });

const generateCodes = () => {
    codesForm.post('/settings/account/recovery-codes', {
        preserveScroll: true,
        onSuccess: () => {
            codesForm.reset();
        },
    });
};

watch(() => props.new_recovery_codes, (codes) => {
    if (codes && codes.length > 0) {
        newCodes.value = codes;
        showCodesModal.value = true;
    }
}, { immediate: true });

const copyCodes = () => {
    if (!newCodes.value) return;
    navigator.clipboard.writeText(newCodes.value.join('\n'));
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const downloadCodes = () => {
    if (!newCodes.value) return;
    const text = newCodes.value.join('\n');
    const blob = new Blob([text], { type: 'text/plain' });
    const url = URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'recovery-codes.txt';
    a.click();
    setTimeout(() => URL.revokeObjectURL(url), 500);
};
</script>

<template>
    <SettingsLayout title="Keamanan Akun">
        <div class="space-y-6">
            <!-- Change Password -->
            <form @submit.prevent="submitPassword" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900">Ubah Password</h2>
                        <p class="text-sm text-gray-500 mt-1">Perbarui password akun Anda secara berkala untuk menjaga keamanan.</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <Label for="cp_current" class="block text-sm font-medium text-gray-700">Password Saat Ini</Label>
                            <div class="relative">
                                <Input
                                    id="cp_current"
                            v-model="passwordForm.current_password"
                                    :type="showPassword ? 'text' : 'password'"
                                    placeholder="Masukkan password saat ini"
                                    required
                                    :class="['h-10 w-full rounded-xl border-[1.5px] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:ring-[3px] pr-10', passwordForm.errors.current_password ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-[#c1ccd6] focus:border-[#00684a] focus:ring-[#00684a]/10']"
                                />
                                <button
                                    type="button"
                                    @click="showPassword = !showPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7c8c9a] hover:text-[#001e2b]"
                                >
                                    <Eye v-if="!showPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.current_password" class="text-sm text-red-500 mt-1">{{ passwordForm.errors.current_password }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="cp_new" class="block text-sm font-medium text-gray-700">Password Baru</Label>
                            <div class="relative">
                                <Input
                                    id="cp_new"
                                    v-model="passwordForm.new_password"
                                    :type="showNewPassword ? 'text' : 'password'"
                                    placeholder="Minimal 8 karakter"
                                    required
                                    :class="['h-10 w-full rounded-xl border-[1.5px] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:ring-[3px] pr-10', passwordForm.errors.new_password ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-[#c1ccd6] focus:border-[#00684a] focus:ring-[#00684a]/10']"
                                />
                                <button
                                    type="button"
                                    @click="showNewPassword = !showNewPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7c8c9a] hover:text-[#001e2b]"
                                >
                                    <Eye v-if="!showNewPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.new_password" class="text-sm text-red-500 mt-1">{{ passwordForm.errors.new_password }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="cp_confirm" class="block text-sm font-medium text-gray-700">Konfirmasi Password Baru</Label>
                            <div class="relative">
                                <Input
                                    id="cp_confirm"
                                    v-model="passwordForm.new_password_confirmation"
                                    :type="showConfirmPassword ? 'text' : 'password'"
                                    placeholder="Ulangi password baru"
                                    required
                                    :class="['h-10 w-full rounded-xl border-[1.5px] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:ring-[3px] pr-10', passwordForm.errors.new_password_confirmation ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-[#c1ccd6] focus:border-[#00684a] focus:ring-[#00684a]/10']"
                                />
                                <button
                                    type="button"
                                    @click="showConfirmPassword = !showConfirmPassword"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-[#7c8c9a] hover:text-[#001e2b]"
                                >
                                    <Eye v-if="!showConfirmPassword" class="h-4 w-4" />
                                    <EyeOff v-else class="h-4 w-4" />
                                </button>
                            </div>
                            <p v-if="passwordForm.errors.new_password_confirmation" class="text-sm text-red-500 mt-1">{{ passwordForm.errors.new_password_confirmation }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="passwordForm.processing"
                        class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ passwordForm.processing ? 'Menyimpan...' : 'Simpan Password' }}
                    </button>
                </div>
            </form>

            <!-- Security Question -->
            <form @submit.prevent="submitSecurity" class="space-y-6">
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                    <div class="p-6 border-b border-gray-100">
                        <h2 class="text-lg font-semibold text-gray-900">Pertanyaan Keamanan</h2>
                        <p class="text-sm text-gray-500 mt-1">Digunakan untuk memulihkan akun jika lupa password.</p>
                    </div>

                    <div class="p-6 space-y-6">
                        <div class="space-y-2">
                            <Label for="sq_question" class="block text-sm font-medium text-gray-700">Pertanyaan Keamanan</Label>
                            <Select v-model="securityForm.security_question">
                                <SelectTrigger :class="['h-10 w-full rounded-xl border-[1.5px] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:ring-[3px] px-4', securityForm.errors.security_question ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-[#c1ccd6] focus:border-[#00684a] focus:ring-[#00684a]/10']">
                                    <SelectValue placeholder="Pilih pertanyaan keamanan" />
                                </SelectTrigger>
                                <SelectContent class="bg-white">
                                    <SelectItem v-for="q in securityQuestions" :key="q" :value="q">
                                        {{ q }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="securityForm.errors.security_question" class="text-sm text-red-500 mt-1">{{ securityForm.errors.security_question }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="sq_answer" class="block text-sm font-medium text-gray-700">Jawaban</Label>
                            <Input
                                id="sq_answer"
                                v-model="securityForm.security_answer"
                                type="text"
                                placeholder="Masukkan jawaban baru"
                                required
                                :class="['h-10 w-full rounded-xl border-[1.5px] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:ring-[3px] px-4', securityForm.errors.security_answer ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-[#c1ccd6] focus:border-[#00684a] focus:ring-[#00684a]/10']"
                            />
                            <p v-if="securityForm.errors.security_answer" class="text-sm text-red-500 mt-1">{{ securityForm.errors.security_answer }}</p>
                        </div>

                        <div class="space-y-2">
                            <Label for="sq_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password Saat Ini</Label>
                            <Input
                                id="sq_password"
                                v-model="securityForm.current_password"
                                type="password"
                                placeholder="Masukkan password untuk konfirmasi"
                                required
                                :class="['h-10 w-full rounded-xl border-[1.5px] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:ring-[3px] px-4', securityForm.errors.current_password ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-[#c1ccd6] focus:border-[#00684a] focus:ring-[#00684a]/10']"
                            />
                            <p v-if="securityForm.errors.current_password" class="text-sm text-red-500 mt-1">{{ securityForm.errors.current_password }}</p>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button
                        type="submit"
                        :disabled="securityForm.processing"
                        class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        {{ securityForm.processing ? 'Menyimpan...' : 'Simpan Pertanyaan Keamanan' }}
                    </button>
                </div>
            </form>

            <!-- Recovery Codes -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h2 class="text-lg font-semibold text-gray-900">Kode Pemulihan</h2>
                    <p class="text-sm text-gray-500 mt-1">{{ recovery_codes_count }} kode tersisa. Generate ulang akan menonaktifkan kode sebelumnya.</p>
                </div>

                <div class="p-6 space-y-6">
                    <div class="space-y-2">
                        <Label for="rc_password" class="block text-sm font-medium text-gray-700">Konfirmasi Password untuk Generate Ulang</Label>
                        <Input
                            id="rc_password"
                            v-model="codesForm.current_password"
                            type="password"
                            placeholder="Masukkan password"
                            :class="['h-10 w-full rounded-xl border-[1.5px] bg-white text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all focus:ring-[3px] px-4', codesForm.errors.current_password ? 'border-red-500 focus:border-red-500 focus:ring-red-200' : 'border-[#c1ccd6] focus:border-[#00684a] focus:ring-[#00684a]/10']"
                        />
                        <p v-if="codesForm.errors.current_password" class="text-sm text-red-500 mt-1">{{ codesForm.errors.current_password }}</p>
                    </div>
                </div>
            </div>

            <div class="flex justify-end">
                <button
                    @click="generateCodes"
                    :disabled="codesForm.processing"
                    class="px-6 py-2 bg-[#001e2b] text-white rounded-lg hover:bg-gray-800 transition-colors font-medium text-sm disabled:opacity-50 disabled:cursor-not-allowed"
                >
                    {{ codesForm.processing ? 'Memproses...' : 'Generate Ulang' }}
                </button>
            </div>
        </div>

        <!-- Modal Kode Pemulihan -->
        <Teleport to="body">
            <div v-if="showCodesModal && newCodes" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
                <div class="w-full max-w-md rounded-2xl bg-white shadow-xl">
                    <div class="rounded-t-2xl bg-[#001e2b] px-6 py-5">
                        <div class="flex items-center justify-between">
                            <h2 class="text-lg font-semibold text-white">Kode Pemulihan Baru</h2>
                            <button @click="showCodesModal = false" class="text-[#a8b3bc] hover:text-white transition-colors">
                                <X class="h-5 w-5" />
                            </button>
                        </div>
                        <p class="mt-1 text-sm text-[#a8b3bc]">Simpan kode ini di tempat aman. Setiap kode hanya bisa digunakan sekali.</p>
                    </div>

                    <div class="p-6 space-y-5">
                        <div class="rounded-xl border-2 border-dashed border-[#ffc107] bg-[#fffbe6] p-3">
                            <div class="flex items-start gap-2">
                                <AlertTriangle class="mt-0.5 h-4 w-4 shrink-0 text-[#856404]" />
                                <p class="text-xs font-semibold text-[#856404]">Kode ini hanya ditampilkan sekali. Salin atau download sekarang.</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <div
                                v-for="(code, index) in newCodes"
                                :key="index"
                                class="rounded-lg bg-[#f4f7f6] px-4 py-3 font-mono text-sm font-bold tracking-wider text-[#001e2b] text-center"
                            >
                                {{ code }}
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button
                                @click="copyCodes"
                                class="flex-1 flex items-center justify-center gap-2 rounded-xl border-[1.5px] border-[#c1ccd6] py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                            >
                                <Copy v-if="!copied" class="h-4 w-4" />
                                <Check v-else class="h-4 w-4 text-[#00684a]" />
                                {{ copied ? 'Tersalin!' : 'Salin' }}
                            </button>
                            <button
                                @click="downloadCodes"
                                class="flex-1 flex items-center justify-center gap-2 rounded-xl border-[1.5px] border-[#c1ccd6] py-2.5 text-sm font-medium text-gray-700 transition-colors hover:bg-gray-50"
                            >
                                <Download class="h-4 w-4" />
                                Download
                            </button>
                        </div>

                        <button
                            @click="showCodesModal = false"
                            class="w-full rounded-xl bg-[#001e2b] py-2.5 text-sm font-semibold text-white hover:bg-gray-800 transition-colors"
                        >
                            Tutup
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </SettingsLayout>
</template>
