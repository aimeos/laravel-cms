<script>
  import gql from 'graphql-tag'

  export default {
    props: {
      'modelValue': {type: [Object, null], default: () => null},
      'config': {type: Object, default: () => {}},
      'assets': {type: Array, default: () => []},
    },

    emits: ['update:modelValue', 'addFile', 'removeFile'],

    data() {
      return {
        file: {},
        index: Math.floor(Math.random() * 100000),
        selected: null
      }
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
          console.error(`addFile()`, error)
        }).finally(() => {
          this.selected = null
        })
      },


      handle(data, path) {
        this.$emit('addFile', data)
        this.$emit('update:modelValue', {id: data.id, type: 'file'})

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

          this.$emit('removeFile', id)
          this.$emit('update:modelValue', null)
        }).catch(error => {
          console.error(`dropFile(${code})`, error)
        })
      }
    },

    watch: {
      modelValue: {
        immediate: true,
        handler(obj) {
          if(obj?.id) {
            const idx = this.assets.findIndex(item => item.id === obj.id)

            if(idx !== -1) {
              this.file = this.assets[idx]
            }
          } else {
            this.file = {}
          }
        }
      }
    }
  }
</script>

<template>
  <v-row>
    <v-col class="files">
      <div v-if="file.path" class="image">
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
        <button v-if="file.path" @click="remove()"
          title="Remove file"
          type="button">
          <v-icon icon="mdi-trash-can" role="img"></v-icon>
        </button>
      </div>
      <div v-else class="file file-input">
        <input type="file"
          @input="add($event)"
          :accept="config.accept || '*'"
          :id="'file-' + index"
          :value="selected"
          hidden>
        <label :for="'file-' + index">Add file</label>
      </div>
    </v-col>
    <v-col>
    </v-col>
  </v-row>
</template>

<style>
  .file {
    display: flex;
  }

  .file, .file.file-input {
    justify-content: center;
    align-content: center;
    position: relative;
    flex-wrap: wrap;
    height: 200px;
  }

  .file.file-input label {
    display: flex;
    flex-wrap: wrap;
    align-content: center;
    justify-content: center;
  }

  .file button {
    position: absolute;
    background-color: rgba(var(--v-theme-primary), 0.75);
    border-radius: 0.5rem;
    padding: 0.75rem;
    color: #fff;
    right: 0;
    top: 0;
  }

  .file .v-progress-linear {
    position: absolute;
    z-index: 1;
  }
</style>
