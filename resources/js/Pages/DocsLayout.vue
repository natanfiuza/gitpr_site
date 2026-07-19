<template>
    <div
        class="h-screen bg-slate-50 text-slate-900 dark:bg-gitpr_dark dark:text-gitpr_text flex flex-col transition-colors duration-300">

        <Head>
            <title>{{ page_title }} - GitPR</title>
            <meta name="description" :content="seo_description" />
        </Head>

        <!-- Top Bar (fixed) -->
        <header
            class="flex-shrink-0 h-14 bg-white border-b border-slate-200 dark:bg-gitpr_dark dark:border-gitpr_dark_border flex items-center px-4 lg:px-6 z-50 transition-colors duration-300">
            <!-- Brand -->
            <div class="flex items-center gap-2">
                <span
                    class="font-bold text-xl text-slate-900 dark:text-gitpr_text transition-colors duration-300">GitPR</span>
                <span class="text-xs text-gitpr_cyan_dark hidden sm:inline">[ CLI ]</span>
            </div>

            <!-- Right: GitHub + Theme + Language + Mobile Toggle -->
            <div class="flex items-center gap-3 ml-auto">
                <a href="https://github.com/natanfiuza/gitpr" target="_blank" rel="noopener noreferrer"
                    class="flex items-center gap-1.5 text-slate-500 hover:text-slate-900 dark:text-slate-400 dark:hover:text-gitpr_text transition-colors group"
                    title="GitHub Repository">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                        <path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd" />
                    </svg>
                    <span v-if="release_tag" class="text-xs font-mono bg-slate-200 dark:bg-gitpr_dark_border text-slate-600 dark:text-gitpr_cyan_light px-1.5 py-0.5 rounded group-hover:text-gitpr_primary transition-colors">{{ release_tag }}</span>
                </a>
                <SearchBar :current_lang="current_lang" class="hidden md:block" />
                <ThemeToggle />
                <LanguageSelector :current_lang="current_lang" />
                <button @click="is_mobile_menu_open = !is_mobile_menu_open"
                    class="md:hidden text-gitpr_cyan_light border border-gitpr_dark_border px-3 py-1 rounded text-sm">
                    ☰
                </button>
            </div>
        </header>

        <!-- Body: Sidebar + Content -->
        <div class="flex-1 flex overflow-hidden">
            <!-- Mobile overlay -->
            <div v-if="is_mobile_menu_open" @click="is_mobile_menu_open = false"
                class="fixed inset-0 top-14 bg-black/50 z-40 md:hidden transition-opacity"></div>

            <!-- Sidebar -->
            <aside
                :class="['w-64 flex-shrink-0 overflow-y-auto border-r bg-white border-slate-200 dark:border-gitpr_dark_border p-6 fixed top-14 bottom-0 left-0 dark:bg-gitpr_dark z-50 transform transition-transform duration-300 md:relative md:top-0 md:translate-x-0', is_mobile_menu_open ? 'translate-x-0' : '-translate-x-full']">
                <button @click="is_mobile_menu_open = false"
                    class="md:hidden absolute top-6 right-6 text-gitpr_text hover:text-gitpr_cyan_light text-xl">
                    ✕
                </button>
                <div class="mt-8 mb-6 md:hidden">
                    <SearchBar :current_lang="current_lang" />
                </div>
                <nav>
                    <ul class="space-y-2">
                        <li v-for="item in menu_items" :key="item.path">
                            <Link :href="'/' + item.path"
                                :class="['block py-1 transition-colors', current_page === item.path ? 'text-gitpr_cyan_light font-bold' : 'text-gitpr_primary hover:text-gitpr_cyan_light']">
                                {{ item.title }}
                            </Link>
                        </li>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6 lg:p-10 overflow-auto">
                <div class="max-w-4xl mx-auto">
                    <h1
                        class="text-sm uppercase tracking-wider text-gitpr_primary mb-6 border-b border-slate-200 dark:border-gitpr_dark_border pb-2 transition-colors duration-300">
                        {{ page_title }}
                    </h1>

                    <MarkdownViewer :content="content" @update_toc="page_toc = $event" />
                </div>

                <!-- TOC Sidebar (desktop only) -->
                <aside
                    class="hidden xl:block fixed top-20 right-10 w-56 border-l border-slate-200 dark:border-gitpr_dark_border pl-4 transition-colors duration-300">
                    <h4 class="text-gitpr_cyan_light uppercase tracking-wide text-xs font-bold mb-4">{{ ui_strings.on_this_page }}</h4>
                    <ul class="space-y-2 text-sm">
                        <li v-for="item in page_toc" :key="item.id" :class="item.level === 'h3' ? 'ml-4' : ''">
                            <button @click="scroll_to_anchor(item.id)"
                                :class="['text-left transition-colors', active_header === item.id ? 'text-gitpr_cyan_light font-bold' : 'text-gitpr_primary hover:text-gitpr_text']">
                                {{ item.text }}
                            </button>
                        </li>
                    </ul>
                </aside>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted } from 'vue';
import { Link, Head, router } from '@inertiajs/vue3';
import MarkdownViewer from '../Components/MarkdownViewer.vue';
import LanguageSelector from '../Components/LanguageSelector.vue';
import SearchBar from '../Components/SearchBar.vue';
import ThemeToggle from '../Components/ThemeToggle.vue';

const props = defineProps({
    content: String,
    current_page: String,
    page_title: String,
    menu_items: {
        type: Array,
        default: () => []
    },
    ui_strings: {
        type: Object,
        default: () => ({ on_this_page: 'On this page', menu: 'Menu' })
    },
    seo_description: String,
    current_lang: String
});

const is_mobile_menu_open = ref(false);
const release_tag = ref('');

const page_toc = ref([]);

const active_header = ref('');
let intersection_observer = null;

watch(page_toc, async () => {
    await nextTick();

    if (intersection_observer) {
        intersection_observer.disconnect();
    }

    intersection_observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                active_header.value = entry.target.id;
            }
        });
    }, { rootMargin: '0px 0px -80% 0px' });

    page_toc.value.forEach((item) => {
        const dom_element = document.getElementById(item.id);
        if (dom_element) intersection_observer.observe(dom_element);
    });
}, { deep: true });

const scroll_to_anchor = (target_id) => {
    const html_element = document.getElementById(target_id);
    if (html_element) {
        html_element.scrollIntoView({ behavior: 'smooth' });
    }
};

onMounted(async () => {
    const saved_lang = localStorage.getItem('gitpr_lang');
    const url_params = new URLSearchParams(window.location.search);

    if (saved_lang && saved_lang !== props.current_lang && !url_params.has('lang')) {
        router.get(window.location.pathname, { lang: saved_lang }, { preserveScroll: true, replace: true });
    }

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
