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
      'assets': {type: Array, default: () => []},
    },
    emits: ['update:modelValue', 'addAsset', 'removeAsset'],
    setup() {
      const app = useAppStore()
      return { app }
    },
    data() {
      return {
        images: [],
        index: Math.floor(Math.random() * 100000),
        value: null
      }
    },
    beforeMount() {
      for(let entry of this.modelValue) {
        const idx = this.assets.findIndex(item => item.id === entry.id)

        if(idx !== -1) {
          this.images.push(this.assets[idx])
        }
      }
    },
    unmounted() {
      this.images.forEach(item => {
        if(item.path && item.path.startsWith('blob:')) {
          URL.revokeObjectURL(item.path)
        }
      })
    },
    methods: {
      add(ev) {
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        Array.from(files).forEach(file => {
          const path = URL.createObjectURL(file)
          const idx = this.images.length

          this.images[idx] = {path: path, uploading: true}

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

            this.$emit('addAsset', data)
            this.images[idx] = Object.assign(data, {path: path}) // avoid blank image
          }).catch(error => {
            console.error(`add()`, error)
          })
        })

        this.value = null
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

          this.$emit('removeAsset', id)
          this.images.splice(idx, 1)
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
      images: {
        deep: true,
        handler() {
          this.$emit('update:modelValue', this.images.filter(item => !!item.id).map(item => {
            return {id: item.id, type: 'file'}
          }))
        }
      }
    }
  }
</script>

<template>
  <VueDraggable
    v-model="images"
    draggable=".image"
    group="images"
    class="files">
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
      <button v-if="item.id" @click="remove(idx)"
        title="Remove image"
        type="button">
        <v-icon icon="mdi-trash-can" role="img"></v-icon>
      </button>
    </div>
    <div class="file-input">
      <input type="file"
        @input="add($event)"
        :id="'images-' + index"
        :value="value"
        accept="image/*"
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
  }

  .image, .file-input {
    display: inline-flex;
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
