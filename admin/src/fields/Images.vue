<script>
  import gql from 'graphql-tag'
  import { VueDraggable } from 'vue-draggable-plus'
  import { useAppStore, useAuthStore } from '../stores'
  import FileListItems from '../components/FileListItems.vue'
  import FileUrlDialog from '../components/FileUrlDialog.vue'
  import FileDialog from '../components/FileDialog.vue'
  import FileDetail from '../views/FileDetail.vue'

  export default {
    components: {
      FileDetail,
      FileDialog,
      FileUrlDialog,
      FileListItems,
      VueDraggable
    },

    inject: ['openView'],

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
        vfiles: false,
        vurls: false,
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
      add(files) {
        if(!this.auth.can('file:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const promises = []

        if(!files?.length) {
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


      open(item) {
        this.openView(FileDetail, {item: item})
      },


      remove(idx) {
        if(this.images[idx]?.id) {
          this.$emit('removeFile', this.images[idx].id)
        }

        this.images.splice(idx, 1)
        this.$emit('update:modelValue', this.images.map(item => ({id: item.id, type: 'file'})))
        this.validate()
      },


      select(items) {
        if(!Array.isArray(items)) {
          items = [items]
        }

        items.forEach(item => {
          this.images.push(item)
          this.$emit('addFile', item.id)
        })

        this.$emit('update:modelValue', this.images.map(item => ({id: item.id, type: 'file'})))
        this.vfiles = false
        this.vurls = false
        this.validate()
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
        const result = this.images.length >= (this.config.min ?? 0) && this.images.length <= (this.config.max ?? 1000)

        this.$emit('error', !result)
        return await result
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
  <VueDraggable v-model="images" :disabled="readonly" @change="change()" draggable=".image" group="images" class="images" animation="500">

    <div v-for="(item, idx) in images" :key="idx" class="image" @click="open(item)">
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
      <button v-if="!readonly && item.id" @click.stop="remove(idx)" class="btn-overlay" title="Remove image" type="button">
        <v-icon icon="mdi-trash-can" role="img"></v-icon>
      </button>
    </div>

    <div v-if="!readonly" class="add">
      <v-btn v-if="auth.can('file:view')"
        icon="mdi-button-cursor"
        variant="flat"
        @click="vfiles = true"
      ></v-btn>
      <v-btn
        @click="vurls = true"
        icon="mdi-link-variant-plus"
        variant="flat"
      ></v-btn>
      <v-btn
        icon="mdi-upload"
        variant="flat">
        <v-file-input
          v-model="selected"
          @update:modelValue="add($event)"
          :accept="config.accept || 'image/*'"
          :hide-input="true"
          prepend-icon="mdi-upload"
          multiple
        ></v-file-input>
      </v-btn>
    </div>
  </VueDraggable>

  <Teleport to="body">
    <FileDialog v-model="vfiles" @add="select($event)" :filter="{mime: 'image/'}" grid />
  </Teleport>

  <Teleport to="body">
    <FileUrlDialog v-model="vurls" @add="select($event)" mime="image/" multiple />
  </Teleport>
</template>

<style scoped>
  .images {
    display: flex;
    justify-content: start;
    flex-wrap: wrap;
  }

  .images .add,
  .images .image {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border: 1px solid #808080;
    border-radius: 4px;
    position: relative;
    height: 180px;
    width: 180px;
    margin: 1px;
  }

  .images .add {
    border: 1px dashed #808080;
  }

  .v-progress-linear {
    position: absolute;
    z-index: 1;
  }
</style>
