<script>
  import gql from 'graphql-tag'
  import { default as FileComp } from './File.vue'

  export default {
    extends: FileComp,

    setup() {
      return { ...FileComp.setup() }
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
          <video ref="video" v-if="file.path" controls
            preload="metadata"
            crossorigin="anonymous"
            :draggable="false"
            :src="url(file.path)"
          ></video>
          <button v-if="!readonly && file.path" @click.stop="remove()" class="btn-overlay" title="Remove video" type="button">
            <v-icon icon="mdi-trash-can" role="img"></v-icon>
          </button>
        </div>
        <div v-else class="file file-input">
          <div class="select-file" v-if="auth.can('file:view')" @click="vfiles = true">
            <label>
              <span class="btn">Select video</span>
            </label>
          </div>
          <div class="upload-file">
            <input type="file"
              @input="add($event)"
              :disabled="readonly"
              :accept="config.accept || 'video/*'"
              :id="'video-' + index"
              :value="selected"
              hidden>
            <label :for="'video-' + index">
              <span class="btn">Add video</span>
            </label>
          </div>
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
    <FileDialog v-model="vfiles" @add="handle($event)" :filter="{mime: 'video/'}" />
  </Teleport>
</template>

<style scoped>
  .files video {
    max-width: 100%;
    max-height: 200px;
  }
</style>
