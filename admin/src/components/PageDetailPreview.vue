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
        return this.app.urlpreview
          .replace(/:domain/, this.item.domain || '')
          .replace(/:id/, this.item.id || '')
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
