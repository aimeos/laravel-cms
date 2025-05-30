import { createPinia } from 'pinia'
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
          primary: '#40749b',
          background: '#F8FAFC'
        }
      },
    },
  },
})


const fields = import.meta.glob("@/fields/*.vue");

for(const path in fields) {
  const name = path.split("/").at(-1).split(".")[0]
  app.component(name, defineAsyncComponent(fields[path]));
}


app.use(logger)
  .use(pinia)
  .use(router)
  .use(vuetify)
  .use(apolloProvider)
  .use(VueObserveVisibility)
  .mount('#app')
