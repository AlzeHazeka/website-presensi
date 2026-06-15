import './bootstrap';

import '@vuepic/vue-datepicker/dist/main.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

if (typeof document !== 'undefined' && document.getElementById('app')) {
    createInertiaApp({
        resolve: (name) => {
            const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
            return pages[`./Pages/${name}.vue`];
        },
        setup({ el, App, props, plugin }) {
            createApp({ render: () => h(App, props) }).use(plugin).mount(el);
        },
    });
}

if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker
            .register('/sw.js')
            .then((reg) => console.log('SW Registered', reg))
            .catch((err) => console.error(err));
    });
}
