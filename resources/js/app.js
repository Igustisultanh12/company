import '../css/app.css';

import { createInertiaApp, Head, Link } from '@inertiajs/vue3';
import { createApp, h } from 'vue';
import { ZiggyVue } from 'ziggy-js';
import directives from './directives';
import AuthLayout from './Layouts/AuthLayout.vue';
import PanelLayout from './Layouts/PanelLayout.vue';
import SiteLayout from './Layouts/SiteLayout.vue';

const appName = document.querySelector('meta[property="og:site_name"]')?.content || 'PT Kreatif Sky Abadi';
const pages = import.meta.glob('./Pages/**/*.vue');

function layoutFor(name) {
    if (name.startsWith('Site/')) return SiteLayout;
    if (name.startsWith('Auth/')) return AuthLayout;
    if (name.startsWith('Admin/') || name.startsWith('Agent/') || name.startsWith('Panel/')) return PanelLayout;
    return undefined;
}

createInertiaApp({
    title: (title) => (title ? (title.includes(' – ') ? title : `${title} – ${appName}`) : appName),
    resolve: async (name) => {
        const loader = pages[`./Pages/${name}.vue`];
        if (!loader) throw new Error(`Halaman tidak ditemukan: ${name}`);
        const page = await loader();
        const component = page.default;
        if (component.layout === undefined) component.layout = layoutFor(name);
        return component;
    },
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });
        app.use(plugin).use(ZiggyVue).use(directives);
        app.component('Link', Link).component('Head', Head);
        app.mount(el);
    },
    progress: { color: '#d946ef', showSpinner: false, delay: 150 },
});
