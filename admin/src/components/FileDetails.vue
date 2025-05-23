<script>
  import gql from 'graphql-tag'
  import Aside from './Aside.vue'
  import FileDetailsFile from './FileDetailsFile.vue'
  import FileDetailsRefs from './FileDetailsRefs.vue'
  import { useMessageStore } from '../stores'


  export default {
    components: {
      Aside,
      FileDetailsFile,
      FileDetailsRefs
    },

    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item', 'close'],

    data: () => ({
      changed: false,
      error: false,
      nav: null,
      tab: 'file',
    }),

    setup() {
      const messages = useMessageStore()
      return { messages }
    },

    methods: {
      save() {
        this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $input: FileInput!) {
            saveFile(id: $id, input: $input) {
              id
            }
          }`,
          variables: {
            id: this.item.id,
            input: {
              name: this.item.name,
              tag: this.item.tag,
            }
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          this.$emit('close', true)
          this.changed = false

          return result.data.saveFile
        }).catch(error => {
          this.messages.add('Error saving file', 'error')
          console.error(`saveFile(id: ${item.id})`, error)
        })
      },
    }
  }
</script>

<template>
  <v-app-bar :elevation="1" density="compact">
    <template v-slot:prepend>
      <v-btn icon="mdi-keyboard-backspace"
        @click="$emit('close')"
        elevation="0"
      ></v-btn>
    </template>

    <v-app-bar-title>
      <div class="app-title">
        File: {{ item.name }}
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn :class="{error: error}" :disabled="!changed || error" @click="save()" variant="text">
        Save
      </v-btn>

      <v-btn @click.stop="nav = !nav">
        <v-icon size="x-large">
          {{ nav ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <v-main class="file-details">
    <v-form @submit.prevent>
      <v-tabs fixed-tabs v-model="tab">
        <v-tab value="file" :class="{changed: changed, error: error}">File</v-tab>
        <v-tab value="refs">Used by</v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <v-window-item value="file">
          <FileDetailsFile
            :item="item"
            @update:item="this.$emit('update:item', item); changed = true"
            @error="error = $event"
          />
        </v-window-item>

        <v-window-item value="refs">
          <FileDetailsRefs
            :item="item"
          />
        </v-window-item>

      </v-window>
    </v-form>
  </v-main>

  <Aside v-model:state="nav" />
</template>

<style scoped>
  .v-toolbar-title {
    margin-inline-start: 0;
  }
</style>
