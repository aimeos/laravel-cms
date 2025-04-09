<script>
  import gql from 'graphql-tag'
  import { useAppStore } from '../../stores'
  import { VueDraggable } from 'vue-draggable-plus'

  export default {
    components: {
      VueDraggable
    },
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

        const items = []
        const promises = []
        const entries = [...this.modelValue || []]

        for(let i = 0; i < files.length; i++) {
          items[i] = {path: URL.createObjectURL(files[i]), uploading: true}
        }
        this.$emit('update:modelValue', [...entries].concat(items))

        for(let i = 0; i < files.length; i++) {
          promises.push(this.$apollo.mutate({
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
              file: files[i]
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

            URL.revokeObjectURL(items[i].path)
            items[i] = data
          }).catch(error => {
            console.error(`addFile()`, error)
          }))
        }

        Promise.all(promises).then(() => {
          this.$emit('update:modelValue', [...entries].concat(items))
        })
      },


      remove(idx) {
        if(!this.modelValue[idx]?.id) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`mutation($id: ID!) {
            dropFile(id: $id) {
              id
            }
          }`,
          variables: {
            id: this.modelValue[idx].id
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
  <VueDraggable
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    draggable=".image"
    group="images"
    class="images">
    <div v-for="(item, idx) in (modelValue || [])" :key="idx" class="image">
      <v-progress-linear v-if="item.uploading"
        color="primary"
        height="5"
        indeterminate
        rounded
      ></v-progress-linear>
      <v-img
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
        :value="null"
        accept="image/*"
        multiple
        hidden>
      <label :for="'images-' + index">Add files</label>
    </div>
  </VueDraggable>
</template>

<style scoped>
  .images,
  .images .sortable {
    display: flex;
    flex-wrap: wrap;
  }

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
