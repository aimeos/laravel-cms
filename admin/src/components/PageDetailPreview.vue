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
        return this.app.urlpage
          .replace(/:domain/, this.item.domain || '')
          .replace(/:path/, this.item.path || '')
          .replace(/\/+$/, '/')
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
    border: 1px solid #ddd;
    height: calc(100vh - 104px);
  }
</style>
