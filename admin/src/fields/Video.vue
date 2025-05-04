<script>
  import gql from 'graphql-tag'
  import File from './File.vue'
  import { useAppStore } from '../stores'

  export default {
    extends: File,

    setup() {
      const app = useAppStore()
      return { app }
    },

    methods: {
      handle(data, path) {
        this.$emit('addFile', data)
        this.$emit('update:modelValue', {id: data.id, type: 'file'})
        URL.revokeObjectURL(path)
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
  <v-row>
    <v-col class="files">
      <div v-if="file.path" class="file">
        <v-progress-linear v-if="file.uploading"
          color="primary"
          height="5"
          indeterminate
          rounded
        ></v-progress-linear>
        <video preload="metadata" controls
          :draggable="false"
          :src="url(file.path)"
        ></video>
        <button v-if="file.path" @click="remove()"
          title="Remove video"
          type="button">
          <v-icon icon="mdi-trash-can" role="img"></v-icon>
        </button>
      </div>
      <div v-else class="file file-input">
        <input type="file"
          @input="add($event)"
          :accept="config.accept || 'video/*'"
          :id="'video-' + index"
          :value="selected"
          hidden>
        <label :for="'video-' + index">Add video</label>
      </div>
    </v-col>
    <v-col>
      {{ file.name }}
    </v-col>
  </v-row>
</template>

<style scoped>
  video {
    max-width: 100%;
    max-height: 200px;
  }
</style>
