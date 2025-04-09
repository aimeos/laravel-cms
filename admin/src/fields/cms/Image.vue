<script>
  import gql from 'graphql-tag'
  import { useAppStore } from '../../stores'

  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
    setup() {
      const app = useAppStore()
      return { app }
    },
    data() {
      return {
        index: Math.floor(Math.random() * 100000),
      }
    },
    methods: {
      add(ev) {
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        this.$emit('update:modelValue', {path: URL.createObjectURL(files[0]), uploading: true})

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

          URL.revokeObjectURL(this.modelValue.path)
          this.$emit('update:modelValue', data)
        }).catch(error => {
          console.error(`addFile()`, error)
        })
      },


      remove() {
        if(!this.modelValue?.id) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`mutation($id: ID!) {
            dropFile(id: $id) {
              id
            }
          }`,
          variables: {
            id: this.modelValue.id
          }
        }).then(response => {
          if(response.errors) {
            throw response.errors
          }
          this.$emit('update:modelValue', null)
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
    }
  }
</script>

<template>
  <div v-if="modelValue" class="image">
    <v-progress-linear v-if="modelValue.uploading"
      color="primary"
      height="5"
      indeterminate
      rounded
    ></v-progress-linear>
    <v-img
      :draggable="false"
      :src="url(modelValue.path)"
      :srcset="srcset(modelValue.previews)"
    ></v-img>
    <button v-if="modelValue.id" @click="remove()"
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
</template>

<style scoped>
  .image, .file-input {
    display: inline-flex;
    border: 1px solid #808080;
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
