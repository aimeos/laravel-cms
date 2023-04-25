import { defineStore } from 'pinia'

const app = document.querySelector('#app');

export const useAppStore = defineStore('app', {
  state: () => ({
    me: null,
    url: app?.dataset.urltemplate || '/:slug/:lang'
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
 *   published {
 *     "live": 2,
 *     "draft": 5
 *   }
 *   type {
 *     "cms:heading": 3,
 *     "cms:text": 8,
 *     "cms:article": 1
 *   }
 * },
 * show {
 *   published: true,
 *   type: false
 * },
 * used {
 *   published {
 *     "live": true
 *     "draft": false
 *   }
 *   type {
 *     "cms:heading": true,
 *     "cms:text": false,
 *     "cms:artice": true
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
