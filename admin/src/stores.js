import { defineStore } from 'pinia'

const app = document.querySelector('#app');

export const useAppStore = defineStore('app', {
  state: () => ({
    me: null,
    urlbase: app?.dataset.urlbase || '/admin',
    urlpage: app?.dataset.urlpage || '/:slug/xx-XX',
    urlfile: app?.dataset.urlfile || '/storage',
  })
})

export const useLanguageStore = defineStore('languages', {
  state: () => ({
    available: JSON.parse(app?.dataset.languages || '{}'),
    current: app?.dataset.language || ''
  })
})

/**
 * Side store with contextual information
 *
 * store {
 *   type {
 *     "heading": 3,
 *     "text": 8,
 *     "article": 1
 *   }
 * },
 * show {
 *   type: false
 * },
 * used {
 *   type {
 *     "heading": true,
 *     "text": false,
 *     "artice": true
 *   }
 * }
 */
export const useSideStore = defineStore('side', {
  state: () => ({
    store: {},
    show: {},
    used: {}
  }),
  actions: {
    isUsed(key, what) {
      if(typeof this.used[key] === 'undefined') {
        this.used[key] = {}
      }

      if(typeof this.used[key][what] === 'undefined') {
        this.used[key][what] = true
      }

      return this.used[key][what]
    },

    toggle(key, what) {
      if(!this.used[key]) {
        this.used[key] = {}
      }
      this.used[key][what] = !this.used[key][what]
    }
  }
})


/**
 * Available element schemas
 */
export const useSchemaStore = defineStore('schemas', {
  state: () => ({
    content: {},
    meta: {},
    config: {}
  })
})


/**
 * Store for queued messages to display to the user
 */
export const useMessageStore = defineStore('messages', {
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
