import gql from 'graphql-tag'
import { defineStore } from 'pinia'
import { apolloClient } from './graphql'


const app = document.querySelector('#app');

export const useAppStore = defineStore('app', {
  state: () => ({
    urladmin: app?.dataset.urladmin || '/cmsadmin',
    urlpreview: app?.dataset.urlpreview || '/cmspreview/:id',
    urlpage: app?.dataset.urlpage || '/:path',
    urlfile: app?.dataset.urlfile || '/storage',
  })
})


export const useAuthStore = defineStore('auth', {
  state: () => ({
    me: null,
    urlintended: null,
    permissions: JSON.parse(app?.dataset.permissions || '{}'),
  }),

  actions: {
    can(action) {
      return this.permissions[action] || false
    },


    intended(url) {
      return url ? this.urlintended = url : this.urlintended
    },


    async isAuthenticated() {
      if(this.me !== null) {
        return !!this.me
      }

      await apolloClient.query({
        query: gql`query{
          me {
            cmseditor
            email
            name
          }
        }`,
      }).then(response => {
        if(response.errors || !response.data.me) {
          throw response
        }

        this.me = response.data.me || null
        return !!this.me
      }).catch(() => {
        this.me = null
      })

      return !!this.me
    },


    login(email, password) {
      return apolloClient.mutate({
        mutation: gql`mutation ($email: String!, $password: String!) {
          cmsLogin(email: $email, password: $password) {
            cmseditor
            email
            name
          }
        }`,
        variables: {
          email: email,
          password: password
        }
      }).then(response => {
        if(response.errors) {
          throw response.errors
        }

        return this.me = response.data.cmsLogin || false
      }).catch(error => {
        this.me = false
        throw error
      });
    },


    logout() {
      return apolloClient.mutate({
        mutation: gql`mutation {
          cmsLogout {
            cmseditor
            email
            name
          }
        }`,
      }).then(response => {
        if(response.errors) {
          throw response.errors
        }

        return response.data.cmsLogout || false
      }).finally(() => {
        this.me = null
      });
    },


    async user() {
      if(await this.isAuthenticated()) {
        return this.me
      }

      return null
    }
  }
})


export const useConfigStore = defineStore('config', {
  state: () => ({
    data: {}
  }),

  actions: {
    get(key, defval = null) {
      if(typeof key !== 'string') {
        return defval
      }

      const val = key.split('.').reduce((part, key) => {
        return typeof part === 'object' && part !== null ? part[key] : part
      }, this.data)

      return typeof val === 'undefined' ? defval : val
    }
  }
})


export const useDrawerStore = defineStore('drawer', {
  state: () => ({
    aside: null,
    nav: null,
  }),

  actions: {
    toggle(key) {
      this[key] = !this[key]
    }
  }
})


export const useLanguageStore = defineStore('language', {
  state: () => ({
    available: JSON.parse(app?.dataset.languages || '{}'),
    current: null,
  }),

  actions: {
    default() {
      return app?.dataset.language || Object.keys(this.available)[0] || 'en'
    }
  }
})


/**
 * Store for queued messages to display to the user
 */
export const useMessageStore = defineStore('message', {
  state: () => ({
    queue: [],
  }),

  actions: {
    add(msg, type = 'info') {
      this.queue.push({
        text: msg,
        color: type,
        timeout: type === 'error' ? 10000 : 3000
      })
    }
  }
})


/**
 * Available element schemas
 */
export const useSchemaStore = defineStore('schema', {
  state: () => ({
    content: {},
    meta: {},
    config: {}
  })
})


/**
 * Side store with contextual information
 *
 * store: {
 *   type: {
 *     "heading": 3,
 *     "text": 8,
 *     "article": 1
 *   }
 * },
 * show: {
 *   type: {
 *     "heading": false,
 *     "text": true,
 *   }
 * }
 */
export const useSideStore = defineStore('side', {
  state: () => ({
    store: {},
    show: {}
  }),

  actions: {
    shown(key, what) {
      if(typeof this.show[key] === 'undefined') {
        this.show[key] = {}
      }

      if(typeof this.show[key][what] === 'undefined') {
        this.show[key][what] = true
      }

      return this.show[key][what]
    },


    toggle(key, what) {
      if(!this.show[key]) {
        this.show[key] = {}
      }
      this.show[key][what] = !this.show[key][what]
    }
  }
})
