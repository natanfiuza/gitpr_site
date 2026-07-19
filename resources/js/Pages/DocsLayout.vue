<template>
    <div class="h-screen bg-gitpr_dark text-gitpr_text flex flex-col">
        <Head>
            <title>{{ page_title }} - GitPR</title>
            <meta name="description" :content="seo_description" />
        </Head>

        <!-- Top Bar (fixed) -->
        <header class="flex-shrink-0 h-14 border-b border-gitpr_dark_border flex items-center px-4 lg:px-6 z-50">
            <!-- Brand -->
            <div class="flex items-center gap-2">
                <span class="font-bold text-xl text-gitpr_text">GitPR</span>
                <span class="text-xs text-gitpr_cyan_dark hidden sm:inline">[ CLI ]</span>
            </div>

            <!-- Right: Language Selector + Mobile Toggle -->
            <div class="flex items-center gap-3 ml-auto">
                <SearchBar :current_lang="current_lang" class="hidden md:block" />
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
                :class="['w-64 flex-shrink-0 overflow-y-auto border-r border-gitpr_dark_border p-6 fixed top-14 bottom-0 left-0 bg-gitpr_dark z-50 transform transition-transform duration-300 md:relative md:top-0 md:translate-x-0', is_mobile_menu_open ? 'translate-x-0' : '-translate-x-full']">
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
                        class="text-sm uppercase tracking-wider text-gitpr_primary mb-6 border-b border-gitpr_dark_border pb-2">
                        {{ page_title }}
                    </h1>

                    <MarkdownViewer :content="content" @update_toc="page_toc = $event" />
                </div>

                <!-- TOC Sidebar (desktop only) -->
                <aside class="hidden xl:block fixed top-20 right-10 w-56 border-l border-gitpr_dark_border pl-4">
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

onMounted(() => {
    const saved_lang = localStorage.getItem('gitpr_lang');
    const url_params = new URLSearchParams(window.location.search);

    if (saved_lang && saved_lang !== props.current_lang && !url_params.has('lang')) {
        router.get(window.location.pathname, { lang: saved_lang }, { preserveScroll: true, replace: true });
    }
});
</script>
