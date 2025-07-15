import { createPinia } from 'pinia'
import { createGettext } from "vue3-gettext";
import { createApp, defineAsyncComponent } from 'vue'
import VueObserveVisibility from 'vue3-observe-visibility'
import apolloProvider from './graphql'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import './assets/base.css'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import logger from './log'
import router from './routes'
import App from './App.vue'


const app = createApp(App)
const pinia = createPinia()
const vuetify = createVuetify({
  components,
  directives,
  icons: {
    defaultSet: 'mdi',
  },
  theme: {
    themes: {
      light: {
        dark: false,
        colors: {
          primary: '#0068D0',
          background: '#F8FAFC'
        }
      },
    },
  },
})


const gettext = createGettext({
  availableLanguages: {
    en: "English (en)",
    de: "Deutsch (de)",
  },
  defaultLanguage: "en",
  translations: {},
  silent: true
});

const langs = Object.keys(gettext.available)
const userLang = navigator.language.toLowerCase().replace('_', '-')
const baseLang = userLang.slice(0, 2)
const sysLang = langs.includes(userLang) ? userLang : langs.includes(baseLang) ? baseLang : 'en'

import(`./language/${sysLang}.json`).then(translations => {
  gettext.translations = translations.default || translations
  gettext.current = sysLang
})


const fields = import.meta.glob("@/fields/*.vue")

for(const path in fields) {
  const name = path.split("/").at(-1).split(".")[0]
  app.component(name, defineAsyncComponent(fields[path]))
}


app.use(logger)
  .use(pinia)
  .use(router)
  .use(gettext)
  .use(vuetify)
  .use(apolloProvider)
  .use(VueObserveVisibility)
  .mount('#app')
