import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
    ],

    theme: {
        extend: {
            colors : {
                "terminal-info": "#38bdf8",
                "terminal-error": "#ef4444",
                "terminal-success": "#22c55e",
                "terminal-warn": "#fbbf24"
            },
            fontFamily: {
                sans: ["Figtree", ...defaultTheme.fontFamily.sans],
            },
            fontSize: {
                "2xs": "0.6rem"
            }
        },
    },

    plugins: [forms],
};
