import './bootstrap'

import {createApp, h} from 'vue'
import {createInertiaApp} from '@inertiajs/vue3'

import {createVuetify} from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import VueMobileDetection from 'vue-mobile-detection';
import {md3} from 'vuetify/blueprints';
import 'vuetify/styles'
import '@mdi/font/css/materialdesignicons.css'
import '@imengyu/vue3-context-menu/lib/vue3-context-menu.css'
import ContextMenu from '@imengyu/vue3-context-menu'

const vuetify = createVuetify({blueprint: md3, components, directives})

const pages = import.meta.glob('./Pages/**/*.vue')

createInertiaApp({
    resolve: (name) => {
        const page = pages[`./Pages/${name}.vue`]
        if (!page) {
            throw new Error(`Inertia page not found: ./Pages/${name}.vue`)
        }
        return page()
    },
    setup({el, App, props, plugin}) {
        createApp({render: () => h(App, props)})
            .use(plugin)
            .use(vuetify)
            .use(VueMobileDetection)
            .use(ContextMenu)
            .mount(el)
    },
})
