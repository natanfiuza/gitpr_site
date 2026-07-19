<template>
    <div class="relative inline-block text-left">
        <select v-model="selected_lang" @change="change_language"
            class="bg-white border-slate-200 text-slate-900 dark:bg-gitpr_dark border dark:border-gitpr_dark_border dark:text-gitpr_text text-sm rounded focus:ring-gitpr_primary focus:border-gitpr_primary block w-40 p-2 cursor-pointer transition-colors duration-300">
            <option value="en">🇺🇸 English</option>
            <option value="pt_br">🇧🇷 Português (BR)</option>
            <option value="pt_pt">🇵🇹 Português (PT)</option>
            <option value="fr">🇫🇷 Français</option>
            <option value="es">🇪🇸 Español</option>
        </select>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    current_lang: {
        type: String,
        default: 'en'
    }
});

const selected_lang = ref(props.current_lang);

const change_language = () => {
    localStorage.setItem('gitpr_lang', selected_lang.value);
    router.get(window.location.pathname, { lang: selected_lang.value }, { preserveScroll: true });
};
</script>
