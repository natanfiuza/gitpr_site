<template>
    <div ref="content_ref" @click="handle_copy"
        class="prose dark:prose-invert max-w-none prose-pre:bg-slate-800 dark:prose-pre:bg-gitpr_dark_border prose-pre:relative prose-pre:mt-8 transition-colors duration-300"
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
    html: true,
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
                const title = token.info.trim().replace(type, '').trim() || labels_en[type];
                const icon = icons[type] || '';
                return `<div class="admonition admonition-${type}"><p class="admonition-title">${icon} ${title}</p>\n`;
            } else {
                return '</div>\n';
            }
        }
    });
});

// ── Code fence with copy button ──────────────────────────────────
const default_fence = md_parser.renderer.rules.fence || function (tokens, idx, options, _env, self) {
    return self.renderToken(tokens, idx, options);
};

md_parser.renderer.rules.fence = function (tokens, idx, options, env, self) {
    const orig_rendered = default_fence(tokens, idx, options, env, self);
    return `<div class="relative group mt-4">
                <button class="copy-btn absolute top-2 right-2 bg-gitpr_primary hover:bg-gitpr_cyan_light text-white text-xs px-2 py-1 rounded opacity-0 group-hover:opacity-100 transition-opacity z-10 cursor-pointer">Copiar</button>
                ${orig_rendered}
            </div>`;
};

// ── Pre-process collaborators blocks ─────────────────────────────
const preprocess_collaborators = (raw_markdown) => {
    return raw_markdown.replace(
        /:::\s*collaborators\s*\n([\s\S]*?):::/g,
        (_match, inner) => {
            const lines = inner.trim().split('\n').filter(line => line.trim());
            const cards = lines.map(line => {
                const m = line.trim().match(/github\.com\/([a-zA-Z0-9_-]+)\/?$/);
                if (m) {
                    return `<div class="collaborator-card" data-github-user="${m[1]}">
  <div class="collaborator-card-loader"><div class="animate-pulse w-12 h-12 rounded-full bg-gitpr_dark_border"></div></div>
</div>`;
                }
                return '';
            }).join('\n');
            return `<div class="collaborators-grid">\n${cards}\n</div>`;
        }
    );
};

const parsed_content = computed(() => {
    const preprocessed = preprocess_collaborators(props.content);
    return md_parser.render(preprocessed);
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

// ── Fetch GitHub collaborator data & replace placeholders ─────────
const fetch_collaborators = async () => {
    if (!content_ref.value) return;

    const cards = content_ref.value.querySelectorAll('.collaborator-card');
    const cache = {};

    for (const card of cards) {
        const username = card.dataset.githubUser;
        if (!username) continue;

        const loader = card.querySelector('.collaborator-card-loader');
        if (loader) loader.remove();

        try {
            if (!cache[username]) {
                const resp = await fetch(`https://api.github.com/users/${username}`);
                if (resp.ok) {
                    cache[username] = await resp.json();
                } else {
                    cache[username] = null;
                }
            }

            const user = cache[username];
            if (user) {
                card.innerHTML = `
                    <a href="https://github.com/${username}" target="_blank" rel="noopener noreferrer"
                       class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 dark:border-gitpr_dark_border hover:bg-slate-100 dark:hover:bg-gitpr_dark_border transition-colors">
                        <img src="${user.avatar_url}&s=64" alt="${user.login}" width="48" height="48"
                             class="rounded-full flex-shrink-0">
                        <div>
                            <span class="font-semibold text-slate-900 dark:text-gitpr_text block">${user.name || user.login}</span>
                            <span class="text-sm text-slate-500 dark:text-slate-400">@${user.login}</span>
                        </div>
                    </a>`;
            }
        } catch (_) {
            // Silently fail
        }
    }
};

watch(parsed_content, async () => {
    await nextTick();
    if (!content_ref.value) return;

    highlight_marked_text();
    await fetch_collaborators();

    const extracted_headers = [];
    const dom_elements = content_ref.value.querySelectorAll('h2, h3');
    const used_slugs = new Set();

    const slugify = (text) => {
        return text
            .toLowerCase()
            .normalize('NFD').replace(/[̀-ͯ]/g, '') // remove accents
            .replace(/[^a-z0-9\s-]/g, '')
            .trim()
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    };

    dom_elements.forEach((el) => {
        let slug = slugify(el.innerText);
        // Deduplicate
        let candidate = slug;
        let counter = 1;
        while (used_slugs.has(candidate)) {
            candidate = `${slug}-${counter++}`;
        }
        used_slugs.add(candidate);
        el.id = candidate;
        extracted_headers.push({
            id: candidate,
            text: el.innerText,
            level: el.tagName.toLowerCase()
        });
    });

    emit('update_toc', extracted_headers);
}, { immediate: true });
</script>
