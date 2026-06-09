<script setup>
import { Card, CardContent } from '@/Components/ui/card';

defineProps({
    title: String,
    value: [String, Number],
    icon: Object,
    trend: { type: String, default: null },
});
</script>

<template>
    <Card>
        <CardContent class="flex items-center gap-4 p-6">
            <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-slate-100">
                <component :is="icon" class="h-6 w-6 text-slate-700" />
            </div>
            <div>
                <p class="text-sm text-slate-500">{{ title }}</p>
                <p class="text-2xl font-bold text-slate-900">{{ value }}</p>
            </div>
        </CardContent>
    </Card>
</template>
