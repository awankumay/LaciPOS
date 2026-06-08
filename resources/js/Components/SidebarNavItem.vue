<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    href: { type: String, required: true },
    icon: { type: Object, required: true },
    label: { type: String, required: true },
    active: { type: Boolean, default: false },
});

const classes = computed(() => {
    return props.active
        ? 'flex items-center gap-3 rounded-lg bg-slate-100 px-3 py-2 text-slate-900 font-medium transition-colors'
        : 'flex items-center gap-3 rounded-lg px-3 py-2 text-slate-500 hover:bg-slate-50 hover:text-slate-900 transition-colors';
});
</script>

<template>
    <Link :href="href" :class="classes">
        <component :is="icon" class="h-5 w-5" />
        <span>{{ label }}</span>
    </Link>
</template>
