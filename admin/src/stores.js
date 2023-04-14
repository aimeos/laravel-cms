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
    toggle(key, what) {
      if(!this.used[key]) {
        this.used[key] = {}
      }
      this.used[key][what] = !this.used[key][what]
    }
  },
  getters: {
    isUsed: (state) => {
      return (key, what) => {
        if(typeof state.used[key] === 'undefined') {
          state.used[key] = {}
        }

        if(typeof state.used[key][what] === 'undefined') {
          state.used[key][what] = true
        }

        return state.used[key][what]
      }
    }
  }
})
