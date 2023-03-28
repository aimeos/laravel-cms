import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { ObserveVisibility } from 'vue-observe-visibility'
import { ApolloClient, InMemoryCache } from '@apollo/client/core'
import { createApolloProvider } from '@vue/apollo-option'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import './assets/base.css'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import router from './routes'
import App from './App.vue'


const pinia = createPinia();
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
        }
      },
    },
  },
})

const cache = new InMemoryCache()
const node = document.querySelector('#app')
const apolloClient = new ApolloClient({
  cache,
  uri: node && node.dataset && node.dataset.graphql || '/graphql',
  credentials: 'include'
})
const apolloProvider = createApolloProvider({
  defaultClient: apolloClient,
})


createApp(App).use(pinia).use(router).use(vuetify).use(apolloProvider)
  .directive('observe-visibility', ObserveVisibility)
  .mount('#app')
