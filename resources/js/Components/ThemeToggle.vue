<template>
    <button @click="toggle_theme"
        class="w-9 h-9 flex items-center justify-center rounded-md border border-slate-200 dark:border-gitpr_dark_border bg-white dark:bg-gitpr_dark text-slate-900 dark:text-gitpr_text hover:bg-slate-100 dark:hover:bg-gitpr_dark_border transition-colors duration-300"
        :title="is_dark ? 'Mudar para Tema Claro' : 'Mudar para Tema Escuro'">
        <span v-if="is_dark" class="text-lg">☀️</span>
        <span v-else class="text-lg">🌙</span>
    </button>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const is_dark = ref(false);

const apply_theme = (dark_mode) => {
    if (dark_mode) {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }
};

const toggle_theme = () => {
    // Derive from the DOM so multiple instances (header + mobile menu) stay in sync
    is_dark.value = !document.documentElement.classList.contains('dark');
    apply_theme(is_dark.value);
    localStorage.setItem('gitpr_theme', is_dark.value ? 'dark' : 'light');
};

onMounted(() => {
    const saved_theme = localStorage.getItem('gitpr_theme');

    if (saved_theme) {
        is_dark.value = saved_theme === 'dark';
    } else {
        is_dark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }

    apply_theme(is_dark.value);
});
</script>
