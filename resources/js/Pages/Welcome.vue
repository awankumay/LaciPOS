<script setup>
defineProps({
    appName: String,
});
</script>

<template>
    <div style="display: flex; align-items: center; justify-content: center; min-height: 100vh;">
        <h1>{{ appName }} — Inertia + Vue 3 berhasil!</h1>
    </div>
</template>
