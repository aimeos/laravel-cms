<script>
  import gql from 'graphql-tag'
  import { useAppStore } from '../stores'

  export default {
    props: {
      'modelValue': {type: [Object, null], default: () => null},
      'config': {type: Object, default: () => {}},
      'assets': {type: Array, default: () => []},
    },
    emits: ['update:modelValue', 'addAsset', 'removeAsset'],
    setup() {
      const app = useAppStore()
      return { app }
    },
    data() {
      return {
        image: {},
        index: Math.floor(Math.random() * 100000),
      }
    },
    beforeMount() {
      if(this.modelValue?.id) {
        const idx = this.assets.findIndex(item => item.id === this.modelValue.id)

        if(idx !== -1) {
          this.image = this.assets[idx]
        }
      }
    },
    unmounted() {
      if(this.image.path && this.image.path.startsWith('blob:')) {
        URL.revokeObjectURL(this.image.path)
      }
    },
    methods: {
      add(ev) {
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        const path = URL.createObjectURL(files[0])
        this.image = {path: path, uploading: true}

        this.$apollo.mutate({
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

          this.$emit('addAsset', data)
          this.image = Object.assign(data, {path: path}) // avoid blank image
          URL.revokeObjectURL(path)
        }).catch(error => {
          console.error(`addFile()`, error)
        })
      },


      remove() {
        if(!this.image.id) {
          return
        }

        const id = this.image.id

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

          this.$emit('removeAsset', id)
          this.image = {}
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
      }
    },
    watch: {
      image: {
        deep: true,
        handler() {
          if(this.image.id) {
            this.$emit('update:modelValue', {id: this.image.id, type: 'file'})
          }
        }
      }
    }
  }
</script>

<template>
  <div class="files">
    <div v-if="image.path" class="image">
      <v-progress-linear v-if="image.uploading"
        color="primary"
        height="5"
        indeterminate
        rounded
      ></v-progress-linear>
      <v-img
        :draggable="false"
        :src="url(image.path)"
        :srcset="srcset(image.previews)"
      ></v-img>
      <button v-if="image.id" @click="remove()"
        title="Remove image"
        type="button">
        <v-icon icon="mdi-trash-can" role="img"></v-icon>
      </button>
    </div>
    <div v-else class="image file-input">
      <input type="file"
        @input="add($event)"
        :id="'image-' + index"
        :value="null"
        accept="image/*"
        hidden>
      <label :for="'image-' + index">Add file</label>
    </div>
  </div>
</template>

<style scoped>
  .image {
    display: flex;
  }

  .image, .file-input {
    border: 1px solid #767676;
    border-radius: 0.5rem;
    position: relative;
    height: 178px;
    width: 178px;
    margin: 1px;
  }

  .file-input label {
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    justify-content: center;
    height: 176px;
    width: 176px;
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
