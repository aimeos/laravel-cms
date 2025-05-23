<script>
  import gql from 'graphql-tag'
  import { useAppStore, useMessageStore } from '../stores'

  export default {
    props: {
      'modelValue': {type: [Object, null], default: () => null},
      'config': {type: Object, default: () => {}},
      'assets': {type: Object, default: () => {}},
      'readonly': {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'error', 'addFile', 'removeFile'],

    data() {
      return {
        file: {},
        index: Math.floor(Math.random() * 100000),
        selected: null
      }
    },

    setup() {
      const messages = useMessageStore()
      const app = useAppStore()
      return { app, messages }
    },

    unmounted() {
      if(this.file?.path?.startsWith('blob:')) {
        URL.revokeObjectURL(this.file.path)
      }
    },

    methods: {
      add(ev) {
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        const path = URL.createObjectURL(files[0])
        this.file = {path: path, uploading: true}

        return this.$apollo.mutate({
          mutation: gql`mutation($file: Upload!) {
            addFile(file: $file) {
              id
              mime
              name
              path
              previews
              updated_at
              editor
            }
          }`,
          variables: {
            file: files[0]
          },
          context: {
            hasUpload: true
          }
        }).then(response => {
          if(response.errors) {
            throw response.errors
          }

          const data = response.data?.addFile || {}
          data.previews = JSON.parse(data.previews) || {}
          delete data.__typename

          return this.handle(data, path)
        }).catch(error => {
          this.messages.add('Error uploading file', 'error')
          console.error(`addFile()`, error)
        }).finally(() => {
          this.selected = null
        })
      },


      handle(data, path) {
        this.file = data
        this.$emit('addFile', data.id)
        this.$emit('update:modelValue', {id: data.id, type: 'file'})
        this.validate()

        if(path.startsWith('blob:')) {
          URL.revokeObjectURL(path)
        }

        return data
      },


      remove() {
        if(!this.file.id && this.file.path.startsWith('blob:')) {
          URL.revokeObjectURL(this.file.path)
          this.file = {}
          return
        }

        const id = this.file.id

        return this.$apollo.mutate({
          mutation: gql`mutation($id: ID!) {
            dropFile(id: $id) {
              id
            }
          }`,
          variables: {
            id: id
          }
        }).then(response => {
          if(response.errors) {
            throw response.errors
          }

          this.file = {}
          this.$emit('removeFile', id)
          this.$emit('update:modelValue', null)
          this.validate()
        }).catch(error => {
          this.messages.add('Error removing file', 'error')
          console.error(`dropFile(${id})`, error)
        })
      },


      url(path) {
        if(path.startsWith('http') || path.startsWith('blob:')) {
          return path
        }
        return this.app.urlfile.replace(/\/+$/g, '') + '/' + path
      },


      async validate() {
        const result = !this.config.required || this.file.path ? true : false

        this.$emit('error', !result)
        return await result
      }
    },

    watch: {
      modelValue: {
        immediate: true,
        handler(data) {
          if(!this.file.path && data && this.assets[data.id]) {
            this.file = this.assets[data.id]
          }
          this.validate()
        }
      }
    }
  }
</script>

<template>
  <v-row>
    <v-col cols="12" md="6">
      <div class="files">
        <div v-if="file.path" class="file">
          <v-progress-linear v-if="file.uploading"
            color="primary"
            height="5"
            indeterminate
            rounded
          ></v-progress-linear>
          <svg draggable="false" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-binary" viewBox="0 0 16 16">
            <path d="M7.05 11.885c0 1.415-.548 2.206-1.524 2.206C4.548 14.09 4 13.3 4 11.885c0-1.412.548-2.203 1.526-2.203.976 0 1.524.79 1.524 2.203m-1.524-1.612c-.542 0-.832.563-.832 1.612q0 .133.006.252l1.559-1.143c-.126-.474-.375-.72-.733-.72zm-.732 2.508c.126.472.372.718.732.718.54 0 .83-.563.83-1.614q0-.129-.006-.25zm6.061.624V14h-3v-.595h1.181V10.5h-.05l-1.136.747v-.688l1.19-.786h.69v3.633z"/>
            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
          </svg>
          {{ file.name }}
          <button class="delete" v-if="!readonly && file.path" @click="remove()"
            title="Remove file"
            type="button">
            <v-icon icon="mdi-trash-can" role="img"></v-icon>
          </button>
        </div>
        <div v-else class="file file-input">
          <input type="file"
            @input="add($event)"
            :disabled="readonly"
            :accept="config.accept || '*'"
            :id="'file-' + index"
            :value="selected"
            hidden>
          <label :for="'file-' + index">Add file</label>
        </div>
      </div>
    </v-col>
    <v-col cols="12" md="6" v-if="file.path">
      Name: {{ file.name }}<br/>
      Mime: {{ file.mime }}<br/>
      Editor: {{ file.editor }}<br/>
      Updated: {{ file.updated_at }}
    </v-col>
  </v-row>
</template>

<style>
  .files, .files .file, .files .file.file-input {
    justify-content: center;
    align-items: center;
    position: relative;
    display: flex;
    flex-wrap: wrap;
    min-height: 48px;
    max-width: 100%;
    width: 100%;
  }

  .files .file, .files .file.file-input {
    max-height: 200px;
  }

  .files .file.file-input label {
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: center;
    min-height: 48px;
    width: 100%;
  }

  .files button.delete {
    position: absolute;
    background-color: rgba(var(--v-theme-primary), 0.75);
    border-radius: 0.5rem;
    padding: 0.75rem;
    color: #fff;
    right: 0;
    top: 0;
  }

  .files .file .v-progress-linear {
    position: absolute;
    z-index: 1;
  }
</style>
