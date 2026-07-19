<template>
    <div class="relative w-full max-w-sm" ref="search_container">
        <input type="text" v-model="search_query" @input="handle_input" placeholder="Buscar na documentação..."
            class="w-full bg-gitpr_dark border border-gitpr_dark_border text-gitpr_text text-sm rounded focus:ring-gitpr_primary focus:border-gitpr_primary block p-2 pl-8" />
        <span class="absolute left-2 top-2 text-gitpr_dark_border">🔍</span>

        <!-- Dropdown de Resultados -->
        <div v-if="search_results.length > 0"
            class="absolute top-full left-0 right-0 mt-2 bg-gitpr_dark border border-gitpr_dark_border rounded shadow-lg z-50 max-h-96 overflow-y-auto">
            <ul>
                <li v-for="result in search_results" :key="result.path"
                    class="border-b border-gitpr_dark_border last:border-0">
                    <Link :href="'/' + result.path + '?lang=' + current_lang + '&mark=' + search_query"
                        class="block p-3 hover:bg-gitpr_dark_border transition-colors" @click="clear_search">
                        <span class="block font-bold text-gitpr_cyan_light text-sm"
                            v-html="highlight_text(result.title)"></span>
                        <span class="block text-xs text-gitpr_text mt-1" v-html="highlight_text(result.snippet)"></span>
                    </Link>
                </li>
            </ul>
        </div>

        <!-- Estado Vazio -->
        <div v-else-if="search_query.length >= 3 && !is_loading"
            class="absolute top-full left-0 right-0 mt-2 bg-gitpr_dark border border-gitpr_dark_border rounded shadow-lg z-50 p-3 text-sm text-gitpr_text">
            Nenhum resultado encontrado.
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { Link } from '@inertiajs/vue3';
import axios from 'axios';

const props = defineProps({
    current_lang: {
        type: String,
        default: 'en'
    }
});

const search_query = ref('');
const search_results = ref([]);
const is_loading = ref(false);
let debounce_timer = null;
const search_container = ref(null);

const close_dropdown = (event) => {
    if (search_container.value && !search_container.value.contains(event.target)) {
        search_results.value = [];
    }
};

onMounted(() => {
    document.addEventListener('click', close_dropdown);
});

onUnmounted(() => {
    document.removeEventListener('click', close_dropdown);
});

const handle_input = () => {
    clearTimeout(debounce_timer);
    if (search_query.value.length < 3) {
        search_results.value = [];
        return;
    }

    is_loading.value = true;
    debounce_timer = setTimeout(async () => {
        try {
            const response = await axios.get('/api/search', {
                params: { q: search_query.value, lang: props.current_lang }
            });
            search_results.value = response.data;
        } catch (error) {
            console.error('Erro na busca:', error);
        } finally {
            is_loading.value = false;
        }
    }, 300);
};

const clear_search = () => {
    search_query.value = '';
    search_results.value = [];
};

const highlight_text = (text) => {
    if (!search_query.value) return text;
    const regex = new RegExp(`(${search_query.value})`, 'gi');
    return text.replace(regex, '<mark class="bg-yellow-400 text-black px-1 rounded">$1</mark>');
};

</script>
