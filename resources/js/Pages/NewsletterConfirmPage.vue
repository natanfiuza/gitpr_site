<template>
    <div
        class="min-h-screen bg-slate-50 text-slate-900 dark:bg-gitpr_dark dark:text-gitpr_text flex flex-col transition-colors duration-300">

        <Head :title="ui_strings.confirm_title + ' - GitPR'" />

        <SiteHeader :current_lang="current_lang" show_version />

        <main class="flex-1 flex items-start justify-center p-4 sm:p-8">
            <div
                class="w-full max-w-md bg-white dark:bg-slate-800/50 rounded-xl border border-slate-200 dark:border-slate-700 shadow-sm p-6">

                <!-- Profile form -->
                <template v-if="status === 'form'">
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.confirm_title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ ui_strings.confirm_intro }}</p>

                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <InputLabel for="name" :value="ui_strings.name_label" />
                            <TextInput id="name" v-model="form.name" type="text" required class="mt-1 block w-full"
                                autocomplete="name" />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="email" :value="ui_strings.email_label" />
                            <TextInput id="email" v-model="form.email" type="email" readonly required
                                class="mt-1 block w-full bg-slate-100 dark:bg-slate-700" />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="github" :value="ui_strings.github_label" />
                            <TextInput id="github" v-model="form.github" type="text" class="mt-1 block w-full"
                                placeholder="octocat" />
                            <InputError :message="form.errors.github" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="phone" :value="ui_strings.phone_label" />
                            <TextInput id="phone" v-model="form.phone" type="tel" class="mt-1 block w-full" />
                            <InputError :message="form.errors.phone" class="mt-1" />
                        </div>

                        <div>
                            <InputLabel for="lang" :value="ui_strings.lang_label" />
                            <select id="lang" v-model="form.lang"
                                class="mt-1 block w-full rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800/50 focus:ring-gitpr_primary focus:border-gitpr_primary">
                                <option v-for="option in lang_options" :key="option.value" :value="option.value">
                                    {{ option.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.lang" class="mt-1" />
                        </div>

                        <p class="text-xs text-slate-500 dark:text-slate-400">{{ ui_strings.confirm_cancel_note }}</p>

                        <PrimaryButton type="submit" :disabled="form.processing" class="w-full justify-center">
                            {{ ui_strings.confirm_submit_btn }}
                        </PrimaryButton>
                    </form>
                </template>

                <!-- Already confirmed -->
                <template v-else-if="status === 'already_confirmed'">
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.confirm_already_title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-6">{{ ui_strings.confirm_already_message }}</p>

                    <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
                        <div v-if="cancel_link_sent"
                            class="text-xs text-green-600 dark:text-green-400 mb-3">
                            {{ ui_strings.newsletter_cancel_link_sent }}
                        </div>
                        <form v-else @submit.prevent="send_cancel_link">
                            <InputLabel for="cancel_email" :value="ui_strings.email_label" />
                            <TextInput id="cancel_email" v-model="cancel_form.email" type="email" readonly required
                                class="mt-1 block w-full bg-slate-100 dark:bg-slate-700" />
                            <button type="submit" :disabled="cancel_link_sending"
                                class="mt-3 w-full text-sm py-2 px-3 rounded-md border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-gitpr_primary dark:hover:text-gitpr_cyan_light transition-colors disabled:opacity-50">
                                {{ ui_strings.newsletter_send_cancel_link_btn }}
                            </button>
                        </form>
                    </div>
                </template>

                <!-- Expired -->
                <template v-else-if="status === 'expired'">
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.confirm_expired_title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ ui_strings.confirm_expired_message }}</p>
                </template>

                <!-- Not found -->
                <template v-else>
                    <h1 class="text-xl font-bold mb-2">{{ ui_strings.confirm_not_found_title }}</h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400">{{ ui_strings.confirm_not_found_message }}</p>
                </template>
            </div>
        </main>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { Head, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SiteHeader from '@/Components/SiteHeader.vue';
import TextInput from '@/Components/TextInput.vue';

const props = defineProps({
    status: {
        type: String,
        required: true
    },
    uuid: {
        type: String,
        required: true
    },
    email: {
        type: String,
        default: null
    },
    subscriber: {
        type: Object,
        default: null
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

const lang_options = [
    { value: 'en', label: 'English' },
    { value: 'pt_br', label: 'Português (Brasil)' },
    { value: 'pt_pt', label: 'Português (Portugal)' },
    { value: 'fr', label: 'Français' },
    { value: 'es', label: 'Español' }
];

const form = useForm({
    name: props.subscriber?.name ?? '',
    email: props.email ?? '',
    github: props.subscriber?.github ?? '',
    phone: props.subscriber?.phone ?? '',
    lang: props.subscriber?.lang ?? props.current_lang
});

const submit = () => {
    form.post(route('newsletter.confirm.submit', { uuid: props.uuid }));
};

const cancel_form = useForm({
    email: props.email ?? ''
});

const page = usePage();
const cancel_link_sent = computed(() => page.props.flash?.newsletter?.status === 'cancel_link_sent');
const cancel_link_sending = ref(false);

const send_cancel_link = () => {
    cancel_link_sending.value = true;
    cancel_form.post(route('newsletter.send-cancel-link'), {
        onFinish: () => {
            cancel_link_sending.value = false;
        }
    });
};
</script>
