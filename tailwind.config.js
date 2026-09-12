import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/**/*.js',
        './resources/**/*.vue',
        './app/Filament/**/*.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: [
                    'Lato',
                    ...defaultTheme.fontFamily.sans,
                ],

                lato: [
                    'Lato',
                    'Arial',
                    'Helvetica',
                    'sans-serif',
                ],

                watoto: [
                    'Yang Bagus',
                    'Lato',
                    'Arial',
                    'Helvetica',
                    'sans-serif',
                ],
            },

            colors: {
                primary: '#0083CB',
                primaryDark: '#076994',
                navy: '#0E3D4F',

                charcoal: '#231F20',
                lightGray: '#E6E7E9',

                green: '#54A845',
                accent: '#F4B122',
            },
        },
    },

    plugins: [
        forms,
    ],
};