<script setup>
import { ref } from 'vue';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Popover, PopoverContent, PopoverTrigger } from '@/Components/ui/popover';
import { Command, CommandGroup, CommandItem, CommandList } from '@/Components/ui/command';
import { Shield, ArrowLeft, ArrowRight, ChevronsUpDown, Check } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: String, default: '' },
    securityAnswer: { type: String, default: '' },
});

const emit = defineEmits(['update:modelValue', 'update:securityAnswer', 'next', 'back']);

const openQuestion = ref(false);

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
                <Popover v-model:open="openQuestion">
                    <PopoverTrigger as-child>
                        <button
                            id="security_question"
                            type="button"
                            role="combobox"
                            :aria-expanded="openQuestion"
                            class="flex items-center justify-between h-11 w-full rounded-xl border-[1.5px] border-[#c1ccd6] bg-white pl-4 pr-3 text-sm font-normal text-[#001e2b] shadow-[0_1px_2px_rgba(0,30,43,0.04)] outline-none transition-all hover:bg-slate-50 focus:border-[#00684a] focus:ring-[3px] focus:ring-[#00684a]/10"
                        >
                            <span class="truncate">{{ modelValue || 'Pilih pertanyaan keamanan' }}</span>
                            <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50 text-[#7c8c9a]" />
                        </button>
                    </PopoverTrigger>
                    <PopoverContent class="w-full sm:w-[500px] p-0 bg-white" align="start">
                        <Command>
                            <CommandList>
                                <CommandGroup>
                                    <CommandItem
                                        v-for="q in securityQuestions"
                                        :key="q"
                                        :value="q"
                                        @select="() => {
                                            emit('update:modelValue', q);
                                            openQuestion = false;
                                        }"
                                        class="text-sm cursor-pointer"
                                    >
                                        {{ q }}
                                        <Check
                                            :class="['ml-auto h-4 w-4 shrink-0', modelValue === q ? 'opacity-100 text-[#00684a]' : 'opacity-0']"
                                        />
                                    </CommandItem>
                                </CommandGroup>
                            </CommandList>
                        </Command>
                    </PopoverContent>
                </Popover>
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
                    class="h-11 text-base rounded-xl border-[1.5px] border-[#c1ccd6] px-4 shadow-[0_1px_2px_rgba(0,30,43,0.04)] focus-visible:border-[#00684a] focus-visible:ring-[3px] focus-visible:ring-[#00684a]/10"
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
