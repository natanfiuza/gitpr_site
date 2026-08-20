<template>
    <header
        class="flex-shrink-0 h-14 bg-white border-b border-slate-200 dark:bg-gitpr_dark dark:border-gitpr_dark_border flex items-center px-4 lg:px-6 z-50 transition-colors duration-300">
        <div :class="contained ? 'w-full max-w-7xl mx-auto flex items-center justify-between' : 'w-full flex items-center justify-between'">
            <!-- Brand -->
            <div class="flex items-center gap-2">
                <Link href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                    <span
                        class="font-bold text-xl text-slate-900 dark:text-gitpr_text transition-colors duration-300">GitPR</span>
                    <span class="text-xs text-gitpr_cyan_dark hidden sm:inline max-[425px]:inline">[ CLI ]</span>
                </Link>
                <span v-if="subtitle"
                    class="ml-2 text-sm font-medium text-slate-500 dark:text-slate-400">| {{ subtitle }}</span>
            </div>

            <!-- Right: GitHub + Back link + Search + Theme + Language + Mobile Toggle -->
            <div class="flex items-center gap-3">
                <a v-if="show_github" href="https://github.com/natanfiuza/gitpr" target="_blank"
                    rel="noopener noreferrer"
                    :class="['flex items-center gap-1.5 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-gitpr_text transition-colors group', compact_header_class]"
                    title="GitHub Repository">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                    </svg>
                    <span v-if="show_version && release_tag"
                        class="text-xs font-mono bg-slate-200 dark:bg-gitpr_dark_border text-slate-600 dark:text-gitpr_cyan_light px-1.5 py-0.5 rounded group-hover:text-gitpr_primary transition-colors">{{ release_tag }}</span>
                </a>
                <Link v-if="back_to_docs_label"
                    :href="'/index' + (current_lang !== 'en' ? '?lang=' + current_lang : '')"
                    :class="['text-sm font-medium text-slate-600 dark:text-slate-300 hover:text-gitpr_primary transition-colors', compact_header_class]">
                    {{ back_to_docs_label }}
                </Link>
                <SearchBar v-if="show_search" :current_lang="current_lang" class="hidden md:block" />
                <ThemeToggle :class="compact_header_class" />
                <LanguageSelector :current_lang="current_lang" :class="compact_header_class" />
                <button v-if="show_mobile_toggle" @click="$emit('toggle-mobile')"
                    class="md:hidden text-gitpr_cyan_light border border-gitpr_dark_border px-3 py-1 rounded text-sm">
                    ☰
                </button>
            </div>
        </div>
    </header>
</template>

<script setup>
import { computed, onMounted, ref } from 'vue';
import { Link } from '@inertiajs/vue3';
import LanguageSelector from '@/Components/LanguageSelector.vue';
import SearchBar from '@/Components/SearchBar.vue';
import ThemeToggle from '@/Components/ThemeToggle.vue';

const props = defineProps({
    current_lang: {
        type: String,
        default: 'en'
    },
    subtitle: {
        type: String,
        default: ''
    },
    show_github: {
        type: Boolean,
        default: false
    },
    show_version: {
        type: Boolean,
        default: false
    },
    show_search: {
        type: Boolean,
        default: false
    },
    show_mobile_toggle: {
        type: Boolean,
        default: false
    },
    back_to_docs_label: {
        type: String,
        default: ''
    },
    contained: {
        type: Boolean,
        default: false
    }
});

defineEmits(['toggle-mobile']);

// On pages with a mobile menu (show_mobile_toggle), the GitHub, theme and language
// controls move into the sidebar on very small screens (≤425px) — hide them here so
// the top bar only shows the brand and the hamburger icon.
const compact_header_class = computed(() =>
    props.show_mobile_toggle ? 'max-[425px]:hidden' : ''
);

const release_tag = ref('');

onMounted(async () => {
    if (!props.show_version) return;

    // Fetch latest GitHub release
    try {
        const resp = await fetch('https://api.github.com/repos/natanfiuza/gitpr/releases/latest');
        if (resp.ok) {
            const data = await resp.json();
            release_tag.value = data.tag_name || '';
        }
    } catch (_) {
        // Silently fail — the badge just won't show
    }
});
</script>
