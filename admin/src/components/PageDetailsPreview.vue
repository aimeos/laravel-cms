<script>
  import { useAppStore } from '../stores'

  export default {
    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item'],

    setup() {
      const app = useAppStore()
      return { app }
    },

    computed: {
      url() {
        const url = this.app.urlpage
          .replace(/:domain/, this.item.data.domain || '')
          .replace(/:slug/, this.item.data.slug || '')
          .replace(/xx_XX/, this.item.data.lang || '')
          .replaceAll('//', '/').replace(':/', '://')

        return url + (url.includes('?') ? '&' : '?') + 'cid=' + (this.item.data.id || '')
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
