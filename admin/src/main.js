import { createApp } from 'vue'
import { createPinia } from 'pinia'
import { ObserveVisibility } from 'vue-observe-visibility'
import { createRouter, createWebHistory } from 'vue-router'
import { ApolloClient, InMemoryCache } from '@apollo/client/core'
import { createApolloProvider } from '@vue/apollo-option'

import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'
import './assets/base.css'

import App from './App.vue'


const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('./views/HomeView.vue')
    },
    {
      path: '/pages',
      name: 'pages',
      component: () => import('./views/PagesView.vue')
    },
    {
      path: '/files',
      name: 'files',
      component: () => import('./views/FilesView.vue')
    }
  ]
})

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

const node = document.querySelector('#app');
const cache = new InMemoryCache()
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
