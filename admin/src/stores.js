import { defineStore } from 'pinia'

export const useTypesStore = defineStore('types', {
  state: () => {
    return {
      state: null,
      type: null,
      used: [],
    }
  }
})

const app = document.querySelector('#app');
export const useLanguageStore = defineStore('languages', {
  state: () => {
    return {
      available: JSON.parse(app && app.dataset.languages || '{}'),
      current: app && app.dataset.language || 'en'
    }
  }
})
