import { defineStore } from 'pinia'

const app = document.querySelector('#app');

export const useAppStore = defineStore('app', {
  state: () => {
    return {
      me: null,
      url: app?.dataset.urltemplate || '/:slug/:lang'
    }
  }
})

export const useLanguageStore = defineStore('languages', {
  state: () => {
    return {
      available: JSON.parse(app?.dataset.languages || '{}'),
      current: app?.dataset.language || ''
    }
  }
})

export const useTypesStore = defineStore('types', {
  state: () => {
    return {
      state: null,
      type: null,
      used: [],
    }
  }
})
