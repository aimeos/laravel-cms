<script>
  import gql from 'graphql-tag'
  import { default as FileComp } from './File.vue'
  import { useAppStore } from '../stores'

  export default {
    extends: FileComp,

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
    <v-col cols="12" md="6">
      <div class="files">
        <div v-if="file.path" class="file">
          <v-progress-linear v-if="file.uploading"
            color="primary"
            height="5"
            indeterminate
            rounded
          ></v-progress-linear>
          <video ref="video" v-if="file.path" controls
            preload="metadata"
            crossorigin="anonymous"
            :draggable="false"
            :src="url(file.path)"
          ></video>
          <button class="delete" v-if="!readonly && file.path" @click="remove()"
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
            :disabled="readonly"
            :value="selected"
            hidden>
          <label :for="'video-' + index">Add video</label>
        </div>
      </div>
    </v-col>
    <v-col cols="12" md="6" v-if="file.path">
      Name: {{ file.name }}<br/>
      Mime: {{ file.mime }}<br/>
      Editor: {{ file.editor }}<br/>
      Modified: {{ file.updated_at }}
    </v-col>
  </v-row>
</template>

<style scoped>
  .files video {
    max-width: 100%;
    max-height: 200px;
  }
</style>
