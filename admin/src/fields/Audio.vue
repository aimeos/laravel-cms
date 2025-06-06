<script>
  import gql from 'graphql-tag'
  import File from './File.vue'

  export default {
    extends: File,

    setup() {
      return { ...File.setup() }
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
          <audio preload="metadata" controls
            :draggable="false"
            :src="url(file.path)"
          ></audio>
          <button class="delete" v-if="!readonly && file.path" @click="remove()"
            title="Remove audio"
            type="button">
            <v-icon icon="mdi-trash-can" role="img"></v-icon>
          </button>
        </div>
        <div v-else class="file file-input">
          <div class="select-file" v-if="auth.can('file:view')" @click="vfiles = true">
            <label>
              <span class="btn">Select audio</span>
            </label>
          </div>
          <div class="upload-file">
            <input type="file"
              @input="add($event)"
              :disabled="readonly"
              :accept="config.accept || 'audio/*'"
              :id="'audio-' + index"
              :value="selected"
              hidden>
            <label :for="'image-' + index">
              <span class="btn">Add audio</span>
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
    <v-dialog v-model="vfiles" scrollable width="100%">
      <FileListItems embed
        @update:item="handle($event); vfiles = false"
      />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
</style>
