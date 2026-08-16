<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900 dark:bg-gitpr_dark dark:text-gitpr_text flex flex-col transition-colors duration-300">

        <Head :title="ui_strings.cancel_title + ' - GitPR'" />

        <SiteHeader :current_lang="current_lang" show_version />

        <main class="flex-1 flex items-start justify-center p-4 sm:p-8">
            <div
                class="w-full max-w-md bg-white dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">

                <!-- Confirm cancellation -->
                <template v-if="status === 'form'">
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.cancel_title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ ui_strings.cancel_intro }}</p>

                    <PrimaryButton @click="cancel_subscription" class="w-full justify-center">
                        {{ ui_strings.cancel_btn }}
                    </PrimaryButton>
                </template>

                <!-- Canceled -->
                <template v-else-if="status === 'done'">
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.cancel_done_title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ ui_strings.cancel_done_message }}</p>
                </template>

                <!-- Already canceled -->
                <template v-else-if="status === 'already_canceled'">
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.cancel_already_title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ ui_strings.cancel_done_message }}</p>
                </template>

                <!-- Not found -->
                <template v-else>
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.cancel_not_found_title }}</h1>
                </template>
            </div>
        </main>
    </div>
</template>

<script setup>
import { Head, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SiteHeader from '@/Components/SiteHeader.vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
    uuid: {
        type: String,
        required: true
    },
    current_lang: {
        type: String,
        default: 'en'
    },
    ui_strings: {
        type: Object,
        default: () => ({})
    }
});

const cancel_subscription = () => {
    router.post(route('newsletter.unsubscribe', { uuid: props.uuid }));
};
</script>
