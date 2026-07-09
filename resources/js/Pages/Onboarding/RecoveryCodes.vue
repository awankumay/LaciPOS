<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import { Shield, Check, Copy, Download, ArrowRight } from 'lucide-vue-next';
import { ref } from 'vue';
import { Link, router } from '@inertiajs/vue3';

const props = defineProps({
    codes: { type: Array, required: true },
});

const copied = ref(false);

const copyCodes = () => {
    navigator.clipboard.writeText(props.codes.join('\n'));
    copied.value = true;
    setTimeout(() => { copied.value = false; }, 2000);
};

const downloadCodes = () => {
    const text = props.codes.join('\n');
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
    <GuestLayout title="Kode Pemulihan">
        <div class="rounded-2xl border border-[#e1e5e8] bg-white shadow-[0_4px_12px_rgba(0,30,43,0.08)]">
            <div class="rounded-t-2xl bg-[#001e2b] px-8 py-7">
                <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-[#00ed64]/20">
                    <Shield class="h-7 w-7 text-[#00ed64]" />
                </div>
                <h1 class="text-center text-xl font-bold text-white">Kode Pemulihan Akun</h1>
                <p class="mt-1 text-center text-sm text-[#a8b3bc]">
                    Simpan kode ini di tempat aman. Setiap kode hanya bisa digunakan sekali.
                </p>
            </div>

            <div class="px-8 py-8">
                <div class="rounded-xl border-2 border-dashed border-[#ffc107] bg-[#fffbe6] p-4">
                    <p class="text-xs font-semibold text-[#856404]">
                        PERHATIAN: Kode ini tidak akan pernah ditampilkan lagi setelah halaman ini ditutup!
                    </p>
                </div>

                <div class="mt-5 grid grid-cols-2 gap-2">
                    <div
                        v-for="(code, index) in codes"
                        :key="index"
                        class="rounded-lg bg-[#f4f7f6] px-4 py-3 font-mono text-sm font-bold tracking-wider text-[#001e2b] text-center"
                    >
                        {{ code }}
                    </div>
                </div>

                <div class="mt-6 flex flex-col gap-3">
                    <button
                        @click="copyCodes"
                        class="flex items-center justify-center gap-2 rounded-full border border-[#c1ccd6] py-3 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]"
                    >
                        <Copy v-if="!copied" class="h-4 w-4" />
                        <Check v-else class="h-4 w-4 text-[#00684a]" />
                        {{ copied ? 'Tersalin!' : 'Salin ke Clipboard' }}
                    </button>
                    <button
                        @click="downloadCodes"
                        class="flex items-center justify-center gap-2 rounded-full border border-[#c1ccd6] py-3 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]"
                    >
                        <Download class="h-4 w-4" />
                        Download File .txt
                    </button>
                </div>

                <Link
                    href="/dashboard"
                    class="mt-6 flex w-full items-center justify-center gap-2 rounded-full bg-[#00ed64] py-3 text-sm font-semibold text-[#001e2b] transition-colors hover:bg-[#00b545]"
                >
                    Lanjut ke Dashboard
                    <ArrowRight class="h-4 w-4" />
                </Link>
            </div>
        </div>
    </GuestLayout>
</template>
