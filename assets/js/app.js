import './css/app.scss';
import './bootstrap.js';

import { createApp, h } from 'vue';
import { createInertiaApp, router } from '@inertiajs/vue3';
import {createPinia} from "pinia";
import VueMobileDetection from "vue-mobile-detection";

const pinia = createPinia()

const appName = "App";

createInertiaApp({
    resolve: name => import(`./Pages/${name}`),
    setup({ el, App, props, plugin }) {
        const application=  createApp({render: () => h(App, props)})
            .use(plugin)
            .use(pinia)
            .use(VueMobileDetection)
            .mount(el);

        delete el.dataset.page

        return application;
    },
    progress: {
        color: '#4B5563',
    },
});
