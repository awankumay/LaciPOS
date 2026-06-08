<script setup>
import { ref, computed } from 'vue';
import { Button } from '@/Components/ui/button';
import { Label } from '@/Components/ui/label';
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

    // Validasi tipe file
    const allowedTypes = ['image/png', 'image/jpeg', 'image/jpg'];
    if (!allowedTypes.includes(file.type)) {
        errorMessage.value = 'Format file harus PNG atau JPG.';
        return;
    }

    // Validasi ukuran file (maks 2MB)
    const maxSize = 2 * 1024 * 1024; // 2MB
    if (file.size > maxSize) {
        errorMessage.value = 'Ukuran file maksimal 2MB.';
        return;
    }

    // Set preview
    previewUrl.value = URL.createObjectURL(file);
    emit('update:modelValue', file);
};

const removeLogo = () => {
    previewUrl.value = null;
    emit('update:modelValue', null);
    if (fileInput.value) {
        fileInput.value.value = '';
    }
};

const triggerFileInput = () => {
    fileInput.value?.click();
};
</script>

<template>
    <div class="space-y-6">
        <div class="text-center">
            <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-slate-100">
                <ImagePlus class="h-8 w-8 text-slate-700" />
            </div>
            <h2 class="text-xl font-semibold text-slate-900">Upload Logo Toko</h2>
            <p class="mt-1 text-sm text-slate-500">
                Logo akan muncul di header struk. Bisa dilewati jika belum ada.
            </p>
        </div>

        <!-- Upload Area -->
        <div class="flex flex-col items-center gap-4">
            <!-- Preview -->
            <div
                v-if="hasLogo"
                class="relative group"
            >
                <img
                    :src="previewUrl"
                    alt="Logo toko"
                    class="h-32 w-32 rounded-lg border border-slate-200 object-contain bg-white p-2"
                />
                <button
                    @click="removeLogo"
                    class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white opacity-0 group-hover:opacity-100 transition-opacity"
                >
                    <X class="h-3 w-3" />
                </button>
            </div>

            <!-- Upload Button -->
            <div
                v-else
                @click="triggerFileInput"
                class="flex h-40 w-full cursor-pointer flex-col items-center justify-center rounded-lg border-2 border-dashed border-slate-300 bg-slate-50 hover:border-slate-400 hover:bg-slate-100 transition-colors"
            >
                <Upload class="mb-2 h-8 w-8 text-slate-400" />
                <p class="text-sm font-medium text-slate-600">Klik untuk upload</p>
                <p class="text-xs text-slate-400 mt-1">PNG atau JPG, maks 2MB</p>
            </div>

            <!-- Hidden File Input -->
            <input
                ref="fileInput"
                type="file"
                accept="image/png,image/jpeg"
                class="hidden"
                @change="handleFileSelect"
            />

            <!-- Ganti Logo Button -->
            <Button
                v-if="hasLogo"
                variant="outline"
                size="sm"
                @click="triggerFileInput"
            >
                Ganti Logo
            </Button>

            <!-- Error Message -->
            <p v-if="errorMessage" class="text-sm text-red-500">
                {{ errorMessage }}
            </p>
        </div>

        <!-- Navigation Buttons -->
        <div class="flex gap-3">
            <Button variant="outline" @click="$emit('back')" class="flex-1">
                <ArrowLeft class="mr-2 h-4 w-4" />
                Kembali
            </Button>
            <Button @click="$emit('next')" class="flex-1">
                {{ hasLogo ? 'Lanjutkan' : 'Lewati' }}
                <ArrowRight class="ml-2 h-4 w-4" />
            </Button>
        </div>
    </div>
</template>
