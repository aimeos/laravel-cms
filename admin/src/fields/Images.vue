<script>
  import gql from 'graphql-tag'
  import { VueDraggable } from 'vue-draggable-plus'
  import { useAppStore, useAuthStore } from '../stores'
  import FileListItems from '../components/FileListItems.vue'

  export default {
    components: {
      FileListItems,
      VueDraggable
    },

    props: {
      'modelValue': {type: Array, default: () => []},
      'config': {type: Object, default: () => {}},
      'assets': {type: Object, default: () => {}},
      'readonly': {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'error', 'addFile', 'removeFile'],

    setup() {
      const auth = useAuthStore()
      const app = useAppStore()

      return { app, auth }
    },

    data() {
      return {
        images: [],
        index: Math.floor(Math.random() * 100000),
        selected: null,
        vfiles: false
      }
    },

    unmounted() {
      this.images.forEach(item => {
        if(item.path?.startsWith('blob:')) {
          URL.revokeObjectURL(item.path)
        }
      })
    },

    methods: {
      add(ev) {
        if(!this.auth.can('file:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const promises = []
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        Array.from(files).forEach(file => {
          const path = URL.createObjectURL(file)
          const idx = this.images.length

          this.images[idx] = {path: path, uploading: true}

          const promise = this.$apollo.mutate({
            mutation: gql`mutation($file: Upload!) {
              addFile(file: $file) {
                id
                mime
                name
                path
                previews
              }
            }`,
            variables: {
              file: file
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

            return new Promise((resolve, reject) => {
              const image = new Image()
              image.onload = resolve
              image.onerror = reject
              image.src = this.url(Object.values(data.previews)[0])
            }).then(() => {
              this.images[idx] = data
              this.$emit('addFile', data.id)
              URL.revokeObjectURL(path)
            })
          }).catch(error => {
            this.$log(`Images::addFile(): Error uploading images`, ev, error)
          })

          promises.push(promise)
        })

        Promise.all(promises).then(() => {
          this.$emit('update:modelValue', this.images.map(item => ({id: item.id, type: 'file'})))
        })

        this.selected = null
      },


      change() {
        this.$emit('update:modelValue', this.images.map(item => ({id: item.id, type: 'file'})))
      },


      remove(idx) {
        if(this.images[idx]?.id) {
          this.$emit('removeFile', this.images[idx].id)
        }

        this.images.splice(idx, 1)
        this.$emit('update:modelValue', this.images.map(item => ({id: item.id, type: 'file'})))
      },


      select(item) {
          this.images[this.images.length] = item
          this.$emit('addFile', item.id)
      },


      srcset(map) {
        let list = []
        for(const key in map) {
          list.push(`${this.url(map[key])} ${key}w`)
        }
        return list.join(', ')
      },


      url(path) {
        if(path.startsWith('http') || path.startsWith('blob:')) {
          return path
        }
        return this.app.urlfile.replace(/\/+$/g, '') + '/' + path
      },


      async validate() {
        return await true
      }
    },

    watch: {
      modelValue: {
        immediate: true,
        handler(list) {
          if(!this.images.length) {
            for(const entry of (list || [])) {
              if(this.assets[entry.id]) {
                this.images.push(this.assets[entry.id])
              }
            }
          }
        }
      }
    }
  }
</script>

<template>
  <VueDraggable v-model="images" :disabled="readonly" @change="change()" draggable=".image" group="images" class="files" animation="500">

    <div v-for="(item, idx) in images" :key="idx" class="image">
      <v-progress-linear v-if="item.uploading"
        color="primary"
        height="5"
        indeterminate
        rounded
      ></v-progress-linear>
      <v-img v-if="item.path"
        :srcset="srcset(item.previews)"
        :src="url(item.path)"
        draggable="false"
      ></v-img>
      <button v-if="!readonly && item.id" @click="remove(idx)"
        title="Remove image"
        type="button">
        <v-icon icon="mdi-trash-can" role="img"></v-icon>
      </button>
    </div>

    <div v-if="!readonly" class="file-input">
      <div class="select-file" v-if="auth.can('file:view')" @click="vfiles = true">
        <label>
          <span class="btn">Select file</span>
        </label>
      </div>
      <div class="upload-file">
        <input type="file"
          @input="add($event)"
          :accept="config.accept || 'image/*'"
          :id="'images-' + index"
          :value="selected"
          multiple
          hidden>
        <label :for="'images-' + index">
          <span class="btn">Add files</span>
        </label>
      </div>
    </div>
  </VueDraggable>

  <Teleport to="body">
    <v-dialog v-model="vfiles" scrollable width="100%">
      <FileListItems embed
        @update:item="select($event); vfiles = false"
        :filter="{mime: 'image/'}"
        grid
      />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
  .files,
  .files .sortable {
    display: flex;
    flex-wrap: wrap;
    justify-content: start;
  }

  .image, .file-input {
    display: flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #808080;
    border-radius: 0.5rem;
    position: relative;
    height: 180px;
    width: 180px;
    margin: 1px;
  }

  .file-input {
    flex-direction: column;
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
    height: 90px;
    width: 100%;
  }

  .file-input .select-file label {
    border-bottom: 1px solid #808080;
  }

  .file-input .select-file .btn,
  .file-input .upload-file .btn {
    background-color: rgba(var(--v-theme-secondary), 1);
    color: rgb(var(--v-theme-on-secondary));
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    cursor: pointer;
  }

  .image button {
    position: absolute;
    background-color: rgba(var(--v-theme-primary), 0.75);
    border-radius: 50%;
    padding: 0.75rem;
    color: #fff;
    right: 0;
    top: 0;
  }

  .v-progress-linear {
    position: absolute;
    z-index: 1;
  }
</style>
