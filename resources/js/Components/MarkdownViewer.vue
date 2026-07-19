<template>
    <div ref="content_ref" @click="handle_copy"
        class="prose prose-invert max-w-none prose-pre:bg-gitpr_dark_border prose-pre:relative prose-pre:mt-8"
        v-html="parsed_content"></div>
</template>

<script setup>
import { computed, ref, watch, nextTick } from 'vue';
import MarkdownIt from 'markdown-it';
import MarkdownItContainer from 'markdown-it-container';
import hljs from 'highlight.js';
import 'highlight.js/styles/github-dark.css';

const props = defineProps({
    content: {
        type: String,
        required: true
    }
});

const emit = defineEmits(['update_toc']);
const content_ref = ref(null);

const md_parser = new MarkdownIt({
    highlight: function (str, lang) {
        if (lang && hljs.getLanguage(lang)) {
            try {
                return hljs.highlight(str, { language: lang }).value;
            } catch (__) {}
        }
        return '';
    }
});

// ── Admonitions (markdown-it-container) ──────────────────────────
const admonition_types = ['note', 'warning', 'tip'];

const icons = {
    note:    '⚙️',
    warning: '⚠️',
    tip:     '💡',
};

const labels_en = {
    note:    'Technical Note',
    warning: 'Warning',
    tip:     'Tip',
};

admonition_types.forEach(type => {
    md_parser.use(MarkdownItContainer, type, {
        validate: (params) => params.trim().startsWith(type),
        render: (tokens, idx) => {
            const token = tokens[idx];
            if (token.nesting === 1) {
                // Opening tag
                const title = token.info.trim().replace(type, '').trim() || labels_en[type];
                const icon = icons[type] || '';
                return `<div class="admonition admonition-${type}"><p class="admonition-title">${icon} ${title}</p>\n`;
            } else {
                // Closing tag
                return '</div>\n';
            }
        }
    });
});

// ── Code fence with copy button ──────────────────────────────────
const default_render = md_parser.renderer.rules.fence || function (tokens, idx, options, env, self) {
    return self.renderToken(tokens, idx, options);
};

md_parser.renderer.rules.fence = function (tokens, idx, options, env, self) {
    const orig_rendered = default_render(tokens, idx, options, env, self);
    return `<div class="relative group mt-4">
                <button class="copy-btn absolute top-2 right-2 bg-gitpr_primary hover:bg-gitpr_cyan_light text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-10 cursor-pointer">Copiar</button>
                ${orig_rendered}
            </div>`;
};

const parsed_content = computed(() => {
    return md_parser.render(props.content);
});

const handle_copy = async (event) => {
    if (event.target.classList.contains('copy-btn')) {
        const btn = event.target;
        const pre = btn.nextElementSibling;

        if (pre && pre.tagName === 'PRE') {
            await navigator.clipboard.writeText(pre.innerText);
            const original_text = btn.innerText;
            btn.innerText = 'Copiado!';
            setTimeout(() => btn.innerText = original_text, 2000);
        }
    }
};

const highlight_marked_text = () => {
    const url_params = new URLSearchParams(window.location.search);
    const mark_term = url_params.get('mark');
    if (!mark_term || !content_ref.value) return;

    const walker = document.createTreeWalker(content_ref.value, NodeFilter.SHOW_TEXT, null, false);
    const nodes_to_replace = [];
    let node;

    while (node = walker.nextNode()) {
        if (node.nodeValue.toLowerCase().includes(mark_term.toLowerCase()) && node.parentNode.tagName !== 'CODE') {
            nodes_to_replace.push(node);
        }
    }

    nodes_to_replace.forEach(n => {
        const regex = new RegExp(`(${mark_term})`, 'gi');
        const wrapper = document.createElement('span');
        wrapper.innerHTML = n.nodeValue.replace(regex, '<mark class="bg-yellow-400 text-black px-1 rounded">$1</mark>');
        n.parentNode.replaceChild(wrapper, n);
    });
};

watch(parsed_content, async () => {
    await nextTick();
    if (!content_ref.value) return;

    highlight_marked_text();

    const extracted_headers = [];
    const dom_elements = content_ref.value.querySelectorAll('h2, h3');

    dom_elements.forEach((el, index) => {
        const anchor_id = `header_${index}`;
        el.id = anchor_id;
        extracted_headers.push({
            id: anchor_id,
            text: el.innerText,
            level: el.tagName.toLowerCase()
        });
    });

    emit('update_toc', extracted_headers);
}, { immediate: true });
</script>
