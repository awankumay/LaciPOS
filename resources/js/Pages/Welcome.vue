<script setup>
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Store } from 'lucide-vue-next';

defineProps({
    appName: String,
});
</script>

<template>
    <div class="flex items-center justify-center min-h-screen bg-slate-50">
        <Card class="w-[400px]">
            <CardHeader>
                <CardTitle class="flex items-center gap-2">
                    <Store class="w-6 h-6" />
                    {{ appName }}
                </CardTitle>
            </CardHeader>
            <CardContent>
                <p class="text-slate-600 mb-4">
                    Tailwind CSS, shadcn-vue, dan Lucide berhasil terinstall!
                </p>
                <Button>Test Button</Button>
            </CardContent>
        </Card>
    </div>
</template>
