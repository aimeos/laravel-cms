<script>
  import gql from 'graphql-tag'
  import File from './File.vue'

  export default {
    extends: File,

    setup() {
      return { ...File.setup() }
    },

    methods: {
      handle(data, path) {
        return new Promise((resolve, reject) => {
          const image = new Image()
          image.onload = resolve
          image.onerror = reject
          image.src = this.url(Object.values(data.previews).shift() || data.path)
        }).then(() => {
          return File.methods.handle.call(this, data, path)
        }).catch(error => {
          console.error(error)
          return false
        })
      },


      srcset(map) {
        let list = []
        for(const key in map) {
          list.push(`${this.url(map[key])} ${key}w`)
        }
        return list.join(', ')
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
          <v-img
            :draggable="false"
            :src="url(file.path)"
            :srcset="srcset(file.previews)"
          ></v-img>
          <button v-if="!readonly && file.path" @click.stop="remove()" class="btn-overlay" title="Remove image" type="button">
            <v-icon icon="mdi-trash-can" role="img"></v-icon>
          </button>
        </div>
        <div v-else-if="!readonly" class="file">
          <v-btn v-if="auth.can('file:view')"
            icon="mdi-button-cursor"
            variant="flat"
            @click="vfiles = true"
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
            ></v-file-input>
          </v-btn>
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
    <FileDialog v-model="vfiles" @add="handle($event)" :filter="{mime: 'image/'}" grid />
  </Teleport>
</template>

<style scoped>
  .v-responsive.v-img, img {
    max-width: 100%;
    height: 180px;
    width: 270px;
  }
</style>
