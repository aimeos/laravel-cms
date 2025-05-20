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


export const useLanguageStore = defineStore('language', {
  state: () => ({
    available: JSON.parse(app?.dataset.languages || '{}'),
    current: app?.dataset.language || null
  })
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
