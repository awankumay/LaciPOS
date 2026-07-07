<script setup>
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/Components/ui/dialog';

defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, required: true },
    description: { type: String, default: '' },
    maxWidth: { type: String, default: 'max-w-lg' },
});

defineEmits(['update:show', 'close']);
</script>

<template>
    <Dialog :open="show" @update:open="(val) => { $emit('update:show', val); if(!val) $emit('close'); }">
        <DialogContent :class="['p-6', maxWidth]">
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription v-if="description" class="break-words">
                    {{ description }}
                </DialogDescription>
            </DialogHeader>

            <div v-if="$slots.default" class="py-2">
                <slot />
            </div>

            <DialogFooter v-if="$slots.footer" class="mt-2">
                <slot name="footer" />
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
