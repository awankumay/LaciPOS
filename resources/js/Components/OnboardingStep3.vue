<script setup>
import { ref, computed } from 'vue';
import { ImagePlus, ArrowRight, ArrowLeft, X, Upload } from 'lucide-vue-next';

const props = defineProps({
    modelValue: { type: [File, null], default: null },
    existingLogoUrl: { type: String, default: null },
});

const emit = defineEmits(['update:modelValue', 'next', 'back']);

const fileInput = ref(null);
const previewUrl = ref(props.existingLogoUrl);
const errorMessage = ref('');

const hasLogo = computed(() => !!previewUrl.value);

const handleFileSelect = (event) => {
    const file = event.target.files[0];
    errorMessage.value = '';

    if (!file) return;

    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
        errorMessage.value = 'Format file harus PNG atau JPG.';
        return;
    }

    const maxSize = 2 * 1024 * 1024;
    if (file.size > maxSize) {
        errorMessage.value = 'Ukuran file maksimal 2MB.';
        return;
    }

    previewUrl.value = URL.createObjectURL(file);
    emit('update:modelValue', file);
};

const removeLogo = () => {
    previewUrl.value = null;
    emit('update:modelValue', null);
    if (fileInput.value) fileInput.value.value = '';
};

const triggerFileInput = () => fileInput.value?.click();
</script>

<template>
    <div class="space-y-8">
        <!-- Header -->
        <div class="flex items-start gap-4">
            <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#e3fcef]">
                <ImagePlus class="h-6 w-6 text-[#00684a]" />
            </div>
            <div>
                <h2 class="text-lg font-semibold text-[#001e2b]">Upload Logo Toko</h2>
                <p class="mt-1 text-sm text-[#5c6c7a]">
                    Logo akan muncul di header struk. Bisa dilewati jika belum ada.
                </p>
            </div>
        </div>

        <!-- Upload Area -->
        <div class="flex flex-col items-center gap-4">
            <!-- Preview -->
            <div v-if="hasLogo" class="relative group">
                <img
                    :src="previewUrl"
                    alt="Logo toko"
                    class="h-36 w-36 rounded-2xl border border-[#e1e5e8] object-contain bg-white p-3 shadow-sm"
                />
                <button
                    @click="removeLogo"
                    class="absolute -top-2 -right-2 flex h-7 w-7 items-center justify-center rounded-full bg-red-500 text-white shadow-md opacity-0 group-hover:opacity-100 transition-opacity"
                >
                    <X class="h-3.5 w-3.5" />
                </button>
            </div>

            <!-- Upload zone -->
            <div
                v-else
                @click="triggerFileInput"
                class="flex h-44 w-full cursor-pointer flex-col items-center justify-center gap-2 rounded-2xl border-2 border-dashed border-[#c1ccd6] bg-[#f9fbfa] transition-colors hover:border-[#00684a] hover:bg-[#e3fcef]"
            >
                <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white shadow-sm">
                    <Upload class="h-6 w-6 text-[#5c6c7a]" />
                </div>
                <div class="text-center">
                    <p class="text-sm font-medium text-[#1c2d38]">Klik untuk upload logo</p>
                    <p class="text-xs text-[#7c8c9a] mt-0.5">PNG atau JPG, maks 2MB</p>
                </div>
            </div>

            <!-- Hidden input -->
            <input
                ref="fileInput"
                type="file"
                accept="image/png,image/jpeg"
                class="hidden"
                @change="handleFileSelect"
            />

            <!-- Ganti logo -->
            <button
                v-if="hasLogo"
                @click="triggerFileInput"
                class="rounded-full border border-[#c1ccd6] px-4 py-1.5 text-xs font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]"
            >
                Ganti Logo
            </button>

            <!-- Error -->
            <div v-if="errorMessage" class="flex items-center gap-2 rounded-lg bg-red-50 px-4 py-2.5 text-sm text-red-600">
                {{ errorMessage }}
            </div>
        </div>

        <!-- Navigation -->
        <div class="flex gap-3">
            <button
                @click="$emit('back')"
                class="flex flex-1 items-center justify-center gap-2 rounded-full border border-[#c1ccd6] py-3 text-sm font-semibold text-[#3d4f5b] transition-colors hover:border-[#001e2b] hover:text-[#001e2b]"
            >
                <ArrowLeft class="h-4 w-4" />
                Kembali
            </button>
            <button
                @click="$emit('next')"
                class="flex flex-1 items-center justify-center gap-2 rounded-full bg-[#00ed64] py-3 text-sm font-semibold text-[#001e2b] transition-colors hover:bg-[#00b545] active:bg-[#008c34]"
            >
                {{ hasLogo ? 'Lanjutkan' : 'Lewati' }}
                <ArrowRight class="h-4 w-4" />
            </button>
        </div>
    </div>
</template>
