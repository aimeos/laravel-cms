<script>
  import gql from 'graphql-tag'
  import { useAppStore, useAuthStore, useMessageStore } from '../stores'
  import FileListItems from '../components/FileListItems.vue'
  import FileDialog from '../components/FileDialog.vue'
  import FileDetail from '../views/FileDetail.vue'

  export default {
    components: {
      FileListItems,
      FileDetail,
      FileDialog
    },

    inject: ['openView'],

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
        selected: null,
        vfiles: false
      }
    },

    setup() {
      const messages = useMessageStore()
      const auth = useAuthStore()
      const app = useAppStore()

      return { app, auth, messages }
    },

    unmounted() {
      if(this.file?.path?.startsWith('blob:')) {
        URL.revokeObjectURL(this.file.path)
      }
    },

    methods: {
      add(ev) {
        if(!this.auth.can('file:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

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
          this.$log(`File::addFile(): Error uploading file`, ev, error)
        }).finally(() => {
          this.selected = null
        })
      },


      handle(item, path) {
        if(!item?.id) {
          this.$log(`File::handle(): Invalid item without ID`, item)
          return
        }

        this.file = {...item}
        this.$emit('addFile', item.id)
        this.$emit('update:modelValue', {id: item.id, type: 'file'})
        this.validate()

        if(path?.startsWith('blob:')) {
          URL.revokeObjectURL(path)
        }

        this.vfiles = false
        return item
      },


      open(item) {
        this.openView(FileDetail, {item: item})
      },


      remove() {
        if(this.file.path.startsWith('blob:')) {
          URL.revokeObjectURL(this.file.path)
        }

        if(this.file.id) {
          this.$emit('removeFile', this.file.id)
        }

        this.$emit('update:modelValue', null)
        this.file = {}
        this.validate()
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
      assets: {
        handler(assets) {
          if(!this.file.path && this.modelValue && assets[this.modelValue.id]) {
            this.file = assets[this.modelValue.id]
          }
          this.validate()
        }
      },


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
        <div v-if="file.path" class="file" @click="open(file)">
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
          <button v-if="!readonly && file.path" @click.stop="remove()" class="btn-overlay" title="Remove file" type="button">
            <v-icon icon="mdi-trash-can" role="img"></v-icon>
          </button>
        </div>
        <div v-else class="file file-input">
          <div class="select-file" v-if="auth.can('file:view')" @click="vfiles = true">
            <label>
              <span class="btn">Select file</span>
            </label>
          </div>
          <div class="upload-file">
            <input type="file"
              @input="add($event)"
              :disabled="readonly"
              :accept="config.accept || '*'"
              :id="'file-' + index"
              :value="selected"
              hidden>
            <label :for="'file-' + index">
              <span class="btn">Add file</span>
            </label>
          </div>
        </div>
      </div>
    </v-col>
    <v-col cols="12" md="6" v-if="file.path">
      Name: {{ file.name }}<br/>
      Mime: {{ file.mime }}<br/>
      Editor: {{ file.editor }}<br/>
      Updated: {{ (new Date(file.updated_at)).toLocaleString() }}
    </v-col>
  </v-row>

  <Teleport to="body">
    <FileDialog v-model="vfiles" @add="handle($event)" />
  </Teleport>

</template>

<style>
  .files, .files .file {
    justify-content: center;
    align-items: center;
    position: relative;
    display: flex;
    min-height: 48px;
    max-height: 200px;
    max-width: 100%;
    width: 100%;
  }

  .file-input .select-file,
  .file-input .upload-file {
    width: 90%;
  }

  .file-input .select-file label,
  .file-input .upload-file label {
    justify-content: center;
    align-content: center;
    flex-wrap: wrap;
    display: flex;
    min-height: 48px;
    width: 100%;
  }

  .file-input .select-file .btn,
  .file-input .upload-file .btn {
    border: 1px solid rgb(var(--v-theme-primary));
    background-color: rgb(var(--v-theme-primary));
    color: rgb(var(--v-theme-on-primary));
    border-radius: 4px;
    padding: 3px 8px;
    height: 32px;
    margin: 8px;
    cursor: pointer;
  }

  .file-input .upload-file .btn {
    border: 1px solid rgb(var(--v-theme-on-surface));
    background-color: rgb(var(--v-theme-surface));
    color: rgb(var(--v-theme-on-surface));
  }

  .files .file .v-progress-linear {
    position: absolute;
    z-index: 1;
  }
</style>
