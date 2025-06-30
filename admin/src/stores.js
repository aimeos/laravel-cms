import gql from 'graphql-tag'
import { defineStore } from 'pinia'
import { apolloClient } from './graphql'


const app = document.querySelector('#app');

export const useAppStore = defineStore('app', {
  state: () => ({
    urladmin: app?.dataset.urladmin || '/cmsadmin',
    urlproxy: app?.dataset.urlproxy || '/cmsproxy?url=:url',
    urlpage: app?.dataset.urlpage || '/:path',
    urlfile: app?.dataset.urlfile || '/storage',
  })
})


export const useAuthStore = defineStore('auth', {
  state: () => ({
    me: null,
    urlintended: null,
  }),

  actions: {
    can(action) {
      return this.me?.permission && this.me.permission[action] || false
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
            permission
            email
            name
          }
        }`,
      }).then(response => {
        if(response.errors) {
          throw response
        }

        this.me = response.data.me ? {...response.data.me, permission: JSON.parse(response.data.me.permission || '{}')} : false
      }).catch(error => {
        console.error('Failed to fetch user data', error)
        this.me = false
      })

      return !!this.me
    },


    login(email, password) {
      return apolloClient.mutate({
        mutation: gql`mutation ($email: String!, $password: String!) {
          cmsLogin(email: $email, password: $password) {
            permission
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

        this.me = response.data.cmsLogin || false

        if(this.me?.permission) {
          this.me.permission = JSON.parse(this.me.permission)
        }

        return this.me
      }).catch(error => {
        this.me = false
        throw error
      });
    },


    logout() {
      return apolloClient.mutate({
        mutation: gql`mutation {
          cmsLogout {
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
  state: () => (JSON.parse(app?.dataset.config || '{}')),

  actions: {
    get(key, defval = null) {
      if(typeof key !== 'string') {
        return defval
      }

      const val = key.split('.').reduce((part, key) => {
        return typeof part === 'object' && part !== null ? part[key] : part
      }, this)

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
    available: useConfigStore().get('languages', {en: 'English'}),
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
  state: () => (JSON.parse(app?.dataset.schemas || '{}')),
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
