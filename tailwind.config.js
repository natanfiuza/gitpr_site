import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';
import typography from '@tailwindcss/typography';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
        './public/content/**/*.md',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                gitpr_dark: '#0a192f',
                gitpr_dark_border: '#0f2b4e',
                gitpr_primary: '#1a80d4',
                gitpr_cyan_light: '#2dd4bf',
                gitpr_cyan_dark: '#22d3ee',
                gitpr_text: '#f8fafc',
            }
        },
    },

    plugins: [forms, typography],
};