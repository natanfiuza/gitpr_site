<template>
    <div class="h-screen overflow-hidden bg-gitpr_dark text-gitpr_text flex">
        <Head>
            <title>{{ page_title }} - GitPR</title>
            <meta name="description" :content="seo_description" />
        </Head>
        <div v-if="is_mobile_menu_open" @click="is_mobile_menu_open = false"
            class="fixed inset-0 bg-black/50 z-40 md:hidden transition-opacity"></div>
        <!-- Sidebar Base -->
        <aside
            :class="['w-64 flex-shrink-0 overflow-y-auto border-r border-gitpr_dark_border p-6 fixed inset-y-0 left-0 bg-gitpr_dark z-50 transform transition-transform duration-300 md:relative md:translate-x-0', is_mobile_menu_open ? 'translate-x-0' : '-translate-x-full']">
            <div class="font-bold text-2xl text-gitpr_cyan_light mb-8 flex items-center gap-2">
                <span class="text-gitpr_text">GitPR</span> <span class="text-sm font-normal text-gitpr_cyan_dark">[ CLI
                    ]</span>
            </div>
            <button @click="is_mobile_menu_open = false"
                class="md:hidden absolute top-6 right-6 text-gitpr_text hover:text-gitpr_cyan_light text-xl">
                ✕
            </button>
            <nav>
                <ul class="space-y-2">
                    <li v-for="item in menu_items" :key="item.path">
                        <Link :href="'/' + item.path"
                            :class="['block py-1 transition-colors', page_title === item.path ? 'text-gitpr_cyan_light font-bold' : 'text-gitpr_primary hover:text-gitpr_cyan_light']">
                            {{ item.title }}
                        </Link>
                    </li>
                </ul>
            </nav>
        </aside>

        <!-- Área de Conteúdo -->
        <main class="flex-1 p-8 lg:p-12 overflow-auto">
            <button @click="is_mobile_menu_open = !is_mobile_menu_open"
                class="md:hidden text-gitpr_cyan_light mb-6 border border-gitpr_dark_border px-3 py-1 rounded">
                ☰ Menu
            </button>
            <div class="max-w-4xl mx-auto">
                <h1
                    class="text-sm uppercase tracking-wider text-gitpr_primary mb-6 border-b border-gitpr_dark_border pb-2">
                    {{ page_title }}
                </h1>

                <MarkdownViewer :content="content" @update_toc="page_toc = $event" />
            </div>
            <aside class="hidden xl:block fixed top-12 right-12 w-64 border-l border-gitpr_dark_border pl-4">
                <h4 class="text-gitpr_cyan_light uppercase tracking-wide text-xs font-bold mb-4">Nesta página</h4>
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
</template>

<script setup>
import { ref, watch, nextTick } from 'vue';
import { Link, Head } from '@inertiajs/vue3';
import MarkdownViewer from '../Components/MarkdownViewer.vue';

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

defineProps({
    content: String,
    page_title: String,
    menu_items: {
        type: Array,
        default: () => []
    },
    seo_description: String
});
</script>
