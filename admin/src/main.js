import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { BatchHttpLink } from "apollo-link-batch-http"
import { createApolloProvider } from '@vue/apollo-option'
import { ApolloClient, InMemoryCache } from '@apollo/client/core'
import VueObserveVisibility from 'vue3-observe-visibility'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import './assets/base.css'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

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

const node = document.querySelector('#app')
const httpLink = new BatchHttpLink({
  uri: node && node.dataset && node.dataset.graphql || '/graphql',
  batchMax: 50,
  batchInterval: 20,
  credentials: 'include'
})
const apolloClient = new ApolloClient({cache: new InMemoryCache(), link: httpLink})
const apolloProvider = createApolloProvider({defaultClient: apolloClient})


app.use(pinia).use(router).use(vuetify).use(apolloProvider).use(VueObserveVisibility).mount('#app')
