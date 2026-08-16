<template>
    <div>
        <h4 class="text-gitpr_cyan_light uppercase tracking-wide text-xs font-bold mb-2">
            {{ ui_strings.newsletter_box_title }}
        </h4>
        <p class="text-xs text-slate-500 dark:text-slate-400 mb-3">{{ ui_strings.newsletter_box_desc }}</p>

        <!-- Sent -->
        <div v-if="flash_status === 'sent'"
            class="text-xs text-green-600 dark:text-green-400 mb-3">
            {{ ui_strings.newsletter_sent }}
        </div>

        <!-- Cancel link sent (generic answer) -->
        <div v-else-if="flash_status === 'cancel_link_sent'"
            class="text-xs text-green-600 dark:text-green-400 mb-3">
            {{ ui_strings.newsletter_cancel_link_sent }}
        </div>

        <!-- Already confirmed -->
        <div v-else-if="flash_status === 'already_confirmed'">
            <p class="text-xs text-amber-600 dark:text-amber-400 mb-3">{{ ui_strings.newsletter_already_confirmed }}</p>
            <button type="button" @click="send_cancel_link" :disabled="cancel_link_sending"
                class="w-full text-sm py-1.5 px-3 rounded-md border border-slate-300 dark:border-slate-600 text-slate-600 dark:text-slate-300 hover:text-gitpr_primary dark:hover:text-gitpr_cyan_light transition-colors disabled:opacity-50">
                {{ ui_strings.newsletter_send_cancel_link_btn }}
            </button>
        </div>

        <!-- Subscribe form -->
        <form v-else @submit.prevent="submit">
            <input v-model="form.email" type="email" required
                :placeholder="ui_strings.newsletter_email_placeholder"
                class="w-full text-sm rounded-md border-slate-300 dark:border-slate-600 bg-white dark:bg-slate-800/50 focus:ring-gitpr_primary focus:border-gitpr_primary" />
            <InputError v-if="form.errors.email" :message="form.errors.email" class="mt-1" />
            <button type="submit" :disabled="form.processing"
                class="w-full mt-2 text-sm py-1.5 px-3 rounded-md bg-gitpr_primary text-white hover:bg-blue-600 transition-colors disabled:opacity-50">
                {{ ui_strings.newsletter_subscribe_btn }}
            </button>
        </form>
    </div>
</template>

<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import InputError from '@/Components/InputError.vue';

const props = defineProps({
    current_lang: {
        type: String,
        default: 'en'
    },
    ui_strings: {
        type: Object,
        default: () => ({})
    }
});

const form = useForm({
    email: '',
    lang: props.current_lang
});

const page = usePage();
const flash_status = computed(() => page.props.flash?.newsletter?.status ?? null);

const cancel_link_sending = ref(false);

const submit = () => {
    form.post(route('newsletter.subscribe'), { preserveScroll: true });
};

const send_cancel_link = () => {
    const email = page.props.flash?.newsletter?.email ?? form.email;

    cancel_link_sending.value = true;
    router.post(
        route('newsletter.send-cancel-link'),
        { email, lang: props.current_lang },
        {
            preserveScroll: true,
            onFinish: () => {
                cancel_link_sending.value = false;
                form.reset('email');
            }
        }
    );
};
</script>
