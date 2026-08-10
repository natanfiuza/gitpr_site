<template>
    <div
        class="h-screen bg-slate-50 text-slate-900 dark:bg-gitpr_dark dark:text-gitpr_text flex flex-col transition-colors duration-300">

        <Head>
            <title>{{ page_title }} - GitPR</title>
            <meta name="description" :content="seo_description" />
            <!-- Open Graph / Facebook -->
            <meta property="og:type" content="website" />
            <meta property="og:url" :content="current_url" />
            <meta property="og:title" :content="page_title + ' - GitPR'" />
            <meta property="og:description" :content="seo_description" />
            <meta property="og:image" content="/assets/img/og-banner.png" />

            <!-- Twitter / X -->
            <meta property="twitter:card" content="summary_large_image" />
            <meta property="twitter:url" :content="current_url" />
            <meta property="twitter:title" :content="page_title + ' - GitPR'" />
            <meta property="twitter:description" :content="seo_description" />
            <meta property="twitter:image" content="/assets/img/og-banner.png" />
        </Head>

        <!-- Top Bar (fixed) -->
        <header
            class="flex-shrink-0 h-14 bg-white border-b border-slate-200 dark:bg-gitpr_dark dark:border-gitpr_dark_border flex items-center px-4 lg:px-6 z-50 transition-colors duration-300">
            <!-- Brand -->
            <Link href="/" class="flex items-center gap-2 hover:opacity-80 transition-opacity">
                <span
                    class="font-bold text-xl text-slate-900 dark:text-gitpr_text transition-colors duration-300">GitPR</span>
                <span class="text-xs text-gitpr_cyan_dark hidden sm:inline">[ CLI ]</span>
            </Link>

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
                        <template v-for="group in menu_groups" :key="group.title ?? '__top__'">
                            <!-- Section header (collapsible) -->
                            <li v-if="group.title"
                                class="text-sm font-bold uppercase tracking-wider text-gitpr_cyan_dark mt-6 mb-2 first:mt-0">
                                <button
                                    @click="toggle_section(group.title)"
                                    class="flex items-center justify-between w-full text-left hover:text-gitpr_cyan_light transition-colors cursor-pointer"
                                    :aria-expanded="is_section_expanded(group.title)">
                                    <span>{{ group.title }}</span>
                                    <svg
                                        :class="['w-3 h-3 transition-transform duration-200', is_section_expanded(group.title) ? 'rotate-90' : 'rotate-0']"
                                        fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                    </svg>
                                </button>
                            </li>
                            <!-- Section items -->
                            <template v-if="!group.title || is_section_expanded(group.title)">
                                <li v-for="item in group.items" :key="item.path">
                                    <Link :href="'/' + item.path"
                                        :class="['block py-1 transition-colors', current_page === item.path ? 'text-gitpr_cyan_light font-bold' : 'text-gitpr_primary hover:text-gitpr_cyan_light']">
                                        {{ item.title }}
                                    </Link>
                                </li>
                            </template>
                        </template>
                    </ul>
                </nav>
            </aside>

            <!-- Main Content -->
            <main class="flex-1 p-6 lg:p-10 overflow-auto">
                <div class="max-w-4xl mx-auto xl:mr-64">
                    <h1
                        class="text-sm uppercase tracking-wider text-gitpr_primary mb-6 border-b border-slate-200 dark:border-gitpr_dark_border pb-2 transition-colors duration-300">
                        {{ page_title }}
                    </h1>

                    <MarkdownViewer :content="content" @update_toc="page_toc = $event" />
                </div>

                <!-- TOC Sidebar (desktop only) -->
                <aside
                    class="hidden xl:flex xl:flex-col fixed top-20 right-10 w-56 max-h-[calc(100vh-6rem)] border-l border-slate-200 dark:border-gitpr_dark_border pl-4 bg-white dark:bg-gitpr_dark z-30 transition-colors duration-300">
                    <div ref="toc_scroll_ref" @scroll="on_toc_scroll" class="overflow-y-auto flex-1 pr-1 toc-scroll">
                        <h4 class="text-gitpr_cyan_light uppercase tracking-wide text-xs font-bold mb-4">{{ ui_strings.on_this_page }}</h4>
                        <ul class="space-y-2 text-sm">
                            <li v-for="item in page_toc" :key="item.id" :class="item.level === 'h3' ? 'ml-4' : ''">
                                <button @click="scroll_to_anchor(item.id)" :data-toc="item.id"
                                    :class="['text-left transition-colors', active_header === item.id ? 'text-gitpr_cyan_light font-bold' : 'text-gitpr_primary hover:text-gitpr_text']">
                                    {{ item.text }}
                                </button>
                            </li>
                        </ul>

                        <!-- Contributors -->
                        <template v-if="collaborators.length">
                            <h4 class="text-gitpr_cyan_light uppercase tracking-wide text-xs font-bold mt-8 mb-3">{{ ui_strings.contributors }}</h4>
                            <div class="flex flex-wrap gap-2 pb-2">
                                <a v-for="c in collaborators" :key="c.login"
                                    :href="'https://github.com/' + c.login" target="_blank" rel="noopener noreferrer"
                                    class="group relative"
                                    :title="'@' + c.login">
                                    <img :src="c.avatar_url + '&s=64'" :alt="c.login"
                                        width="36" height="36"
                                        class="rounded-full border-2 border-transparent group-hover:border-gitpr_cyan_light transition-all duration-200">
                                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 text-xs text-gitpr_cyan_light opacity-0 group-hover:opacity-100 whitespace-nowrap transition-opacity duration-200">@{{ c.login }}</span>
                                </a>
                            </div>
                        </template>
                    </div>
                    <!-- Scroll indicator arrow -->
                    <button v-if="toc_scrollable" @click="scroll_toc"
                        class="flex justify-center py-1 text-gitpr_primary hover:text-gitpr_cyan_light transition-colors text-xs"
                        :title="toc_at_bottom ? 'Voltar ao topo' : 'Rolar para baixo'">
                        <span v-if="toc_at_bottom" class="transform rotate-180">▼</span>
                        <span v-else>▼</span>
                    </button>
                </aside>
            </main>
        </div>
    </div>
</template>

<script setup>
import { ref, watch, nextTick, onMounted, computed } from 'vue';
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
        default: () => ({ on_this_page: 'On this page', menu: 'Menu', contributors: 'Contributors' })
    },
    collaborator_usernames: {
        type: Array,
        default: () => []
    },
    seo_description: String,
    current_lang: String,
    current_url: String
});

const is_mobile_menu_open = ref(false);
const release_tag = ref('');
const collaborators = ref([]);

// ── Collapsible menu sections ────────────────────────────────────
const expanded_sections = ref({});

// Group flat menu_items by section markers
const menu_groups = computed(() => {
    const groups = [];
    let current_section = null;

    for (const item of props.menu_items) {
        if (item.type === 'section') {
            current_section = { title: item.title, items: [] };
            groups.push(current_section);
        } else if (current_section) {
            current_section.items.push(item);
        } else {
            // Top-level items (before any section)
            if (!groups.length || groups[groups.length - 1].title !== null) {
                groups.push({ title: null, items: [] });
            }
            groups[groups.length - 1].items.push(item);
        }
    }

    return groups;
});

const toggle_section = (title) => {
    expanded_sections.value[title] = !expanded_sections.value[title];
};

const is_section_expanded = (title) => {
    // Auto-expand when the current page is inside this section
    // (on first load or after navigation)
    if (expanded_sections.value[title] === undefined) {
        const section = menu_groups.value.find(g => g.title === title);
        if (section && section.items.some(item => item.path === props.current_page)) {
            expanded_sections.value[title] = true;
            return true;
        }
        // Default: collapsed for sections that don't contain current page
        expanded_sections.value[title] = false;
        return false;
    }
    return expanded_sections.value[title];
};

const page_toc = ref([]);
const toc_scroll_ref = ref(null);
const toc_scrollable = ref(false);
const toc_at_bottom = ref(false);

const active_header = ref('');
let intersection_observer = null;

// Scroll the TOC sidebar to keep the active heading visible
const scroll_toc_to_active = (header_id) => {
    const toc_el = toc_scroll_ref.value;
    if (!toc_el || !header_id) return;
    const active_btn = toc_el.querySelector(`button[data-toc="${header_id}"]`);
    if (!active_btn) return;
    const container_rect = toc_el.getBoundingClientRect();
    const btn_rect = active_btn.getBoundingClientRect();
    const offset = btn_rect.top - container_rect.top;
    if (offset < 30 || offset > container_rect.height - 40) {
        active_btn.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
};

watch(page_toc, async () => {
    await nextTick();

    if (intersection_observer) {
        intersection_observer.disconnect();
    }

    intersection_observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                active_header.value = entry.target.id;
                scroll_toc_to_active(entry.target.id);
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

// ── TOC scroll indicator ───────────────────────────────────────
const on_toc_scroll = () => {
    const el = toc_scroll_ref.value;
    if (!el) return;
    const { scrollTop, scrollHeight, clientHeight } = el;
    toc_scrollable.value = scrollHeight > clientHeight;
    toc_at_bottom.value = scrollTop + clientHeight >= scrollHeight - 4;
};

const scroll_toc = () => {
    const el = toc_scroll_ref.value;
    if (!el) return;
    if (toc_at_bottom.value) {
        el.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        el.scrollTo({ top: el.scrollHeight, behavior: 'smooth' });
    }
};

// Check scrollability after TOC updates
watch(page_toc, async () => {
    await nextTick();
    on_toc_scroll();
});

// ── Fetch collaborator avatars from GitHub API ──────────────────
const load_collaborators = async (usernames) => {
    if (!usernames || !usernames.length) { collaborators.value = []; return; }

    const results = [];
    const cache = {};
    for (const username of usernames) {
        try {
            if (!cache[username]) {
                const resp = await fetch(`https://api.github.com/users/${username}`);
                cache[username] = resp.ok ? await resp.json() : null;
            }
            if (cache[username]) results.push(cache[username]);
        } catch (_) {}
    }
    collaborators.value = results;
};

watch(() => props.collaborator_usernames, (val) => load_collaborators(val), { immediate: true });

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
