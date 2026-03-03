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
import { createVPhoneInput, selectPhoneCountryInput, VPhoneCountryFlagSvg } from 'v-phone-input';
import 'flag-icons/css/flag-icons.min.css';
import 'v-phone-input/styles';
const vPhoneInput = createVPhoneInput({
    ...selectPhoneCountryInput,
    countryDisplayComponent: VPhoneCountryFlagSvg,
    modelFormat: 'international',
})

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
            .use(vPhoneInput)
            .use(VueMobileDetection)
            .mount(el)
    },
})
