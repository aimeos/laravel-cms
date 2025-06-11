<script>
  import { useAppStore, useLanguageStore } from '../stores'

  export default {
    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item'],

    setup() {
      const languages = useLanguageStore()
      const app = useAppStore()

      return { app, languages }
    },

    computed: {
      url() {
        const url = this.app.urlpage
          .replace(/:domain/, this.item.domain || '')
          .replace(/:slug/, this.item.slug || '')
          .replace(/xx-XX/, this.item.lang !== this.languages.default() ? this.item.lang : '')
          .replace(/(\/){2,}/g, '/').replace(':/', '://')

        return url + (url.includes('?') ? '&' : '?') + 'preview=true'
      }
    }
  }
</script>

<template>
  <iframe ref="preview" :src="url"></iframe>
</template>

<style>
  iframe {
    width: 100%;
    padding: 0;
    margin: 0;
    border: none;
    min-height: calc(100vh - 6.5rem);
  }
</style>
