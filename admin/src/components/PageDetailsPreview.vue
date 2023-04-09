<script>
  import { useAppStore } from '../stores'

  export default {
    props: ['item'],
    emits: ['update:item'],
    setup() {
      const app = useAppStore()
      return { app }
    },
    data: () => ({
      height: '100vh'
    }),
    computed: {
      url() {
        const url = this.app.url.replace(/:domain/, this.item.domain).replace(/:slug/, this.item.slug).replace(/:lang/, this.item.lang)
        const end = url.endsWith('/') ? url.length - 1 : url.length
        const start = url.startsWith('//') ? 1 : 0

        return url.substring(start, end) || '/'
      }
    },
    methods: {
      resize() {
        this.height = this.$refs.preview.contentWindow.document.body.scrollHeight + 'px'
      }
    }
  }
</script>

<template>
  <iframe ref="preview" :src="url" :style="'min-height: ' + height" @load="resize()"></iframe>
</template>

<style>
  iframe {
    width: 100%;
    padding: 1rem 0 0;
    margin: 0;
    border: none;
  }
</style>
