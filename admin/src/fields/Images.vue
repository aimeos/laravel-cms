<script>
  import gql from 'graphql-tag'
  import { useAppStore } from '../stores'
  import { VueDraggable } from 'vue-draggable-plus'

  export default {
    components: {
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
      const app = useAppStore()
      return { app }
    },

    data() {
      return {
        images: [],
        index: Math.floor(Math.random() * 100000),
        selected: null
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
            console.error(`addFile()`, error)
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
        if(!this.images[idx]?.id) {
          this.images.splice(idx, 1)
          return
        }

        const id = this.images[idx].id

        this.$apollo.mutate({
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

          this.images.splice(idx, 1)
          this.$emit('removeFile', id)
          this.$emit('update:modelValue', this.images.map(item => ({id: item.id, type: 'file'})))
        }).catch(error => {
          console.error(`dropFile(${code})`, error)
        })
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

    <div class="file-input">
      <input type="file"
        @input="add($event)"
        :accept="config.accept || 'image/*'"
        :id="'images-' + index"
        :disabled="readonly"
        :value="selected"
        multiple
        hidden>
      <label :for="'images-' + index">Add files</label>
    </div>
  </VueDraggable>
</template>

<style scoped>
  .files,
  .files .sortable {
    display: flex;
    flex-wrap: wrap;
    justify-content: start;
  }

  .image, .file-input {
    display: inline-flex;
    border: 1px solid #767676;
    border-radius: 0.5rem;
    position: relative;
    height: 180px;
    width: 100%;
    margin: 1px;
  }

  .file-input label {
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    justify-content: center;
    height: 180px;
    width: 100%;
  }

  .image button {
    position: absolute;
    background-color: rgba(var(--v-theme-primary), 0.75);
    border-radius: 0.5rem;
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
