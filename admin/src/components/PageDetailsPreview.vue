<script>
  import { useAppStore } from '../stores'

  export default {
    props: ['item'],
    emits: ['update:item'],
    setup() {
      const app = useAppStore()
      return { app }
    },
    computed: {
      url() {
        const url = this.app.url
          .replace(/:domain/, this.item.domain)
          .replace(/:slug/, this.item.slug)
          .replace(/xx_XX/, this.item.lang)
          .replace(/\/+$/g, '')

        return url + (url.includes('?') ? '&' : '?') + 'cid=' + this.item.id
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
