<script setup>
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Shield, ArrowLeft, ArrowRight } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: String, default: '' },
    securityAnswer: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'update:securityAnswer', 'next', 'back']);

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

const handleNext = () => {
    if (props.modelValue && props.securityAnswer.trim()) {
        emit('next');
    }
};
</script>

<template>
    <div class="space-y-8">
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                <Shield class="h-6 w-6 text-[#00684a]" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-[#001e2b]">Keamanan Akun</h2>
                <p class="mt-1 text-sm text-[#5c6c7a]">
                    Atur pertanyaan keamanan untuk memulihkan akses jika lupa password.
                    Pilih pertanyaan dan isi jawaban yang mudah Anda ingat.
                </p>
            </div>
        </div>

        <div class="space-y-5">
            <div class="space-y-2">
                <Label for="security_question" class="text-sm font-medium text-[#1c2d38]">Pertanyaan Keamanan</Label>
                <select
                    id="security_question"
                    :value="modelValue"
                    @change="$emit('update:modelValue', $event.target.value)"
                    class="flex h-11 w-full rounded-xl border border-[#d0d7dd] bg-white px-3 py-2 text-sm text-[#1c2d38] shadow-sm focus:border-[#00b545] focus:outline-none focus:ring-2 focus:ring-[#00ed64]/20"
                    required
                >
                    <option value="" disabled>Pilih pertanyaan keamanan</option>
                    <option v-for="q in securityQuestions" :key="q" :value="q">{{ q }}</option>
                </select>
            </div>

            <div class="space-y-2">
                <Label for="security_answer" class="text-sm font-medium text-[#1c2d38]">Jawaban</Label>
                <Input
                    id="security_answer"
                    :model-value="securityAnswer"
                    @update:model-value="$emit('update:securityAnswer', $event)"
                    type="text"
                    placeholder="Masukkan jawaban Anda"
                    autocomplete="off"
                    class="h-11 text-base"
                />
                <p class="text-xs text-[#7c8c9a]">
                    Gunakan jawaban yang mudah diingat. Jawaban ini digunakan untuk memulihkan akun Anda.
                </p>
            </div>
        </div>

        <div class="flex gap-3">
            <button
                @click="$emit('back')"
                class="flex flex-1 items-center justify-center gap-2 rounded-full border border-[#c1ccd6] py-3 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali
            </button>
            <button
                @click="handleNext"
                :disabled="!modelValue || !securityAnswer.trim()"
                :class="[
                    'flex flex-1 items-center justify-center gap-2 rounded-full py-3 text-sm font-semibold transition-all duration-150',
                    modelValue && securityAnswer.trim()
                        ? 'bg-[#00ed64] text-[#001e2b] hover:bg-[#00b545] active:bg-[#008c34] cursor-pointer'
                        : 'bg-[#e1e5e8] text-[#a8b3bc] cursor-not-allowed',
                ]"
            >
                Lanjutkan
                <ArrowRight class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
