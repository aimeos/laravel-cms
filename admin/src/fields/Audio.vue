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
        <div v-if="file.path" class="file" @click="open(file)">
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
          <button v-if="!readonly && file.path" @click.stop="remove()" class="btn-overlay" title="Remove audio" type="button">
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
              :accept="config.accept || 'audio/*'"
              :hide-input="true"
              prepend-icon="mdi-upload"
            ></v-file-input>
          </v-btn>
        </div>
      </div>
    </v-col>
    <v-col cols="12" md="6" v-if="file.path">
      {{ $gettext('Name') }}: {{ file.name }}<br/>
      {{ $gettext('Mime') }}: {{ file.mime }}<br/>
      {{ $gettext('Editor') }}: {{ file.editor }}<br/>
      {{ $gettext('Updated') }}: {{ (new Date(file.updated_at)).toLocaleString() }}
    </v-col>
  </v-row>

  <Teleport to="body">
    <FileDialog v-model="vfiles" @add="handle($event); vfiles = false" :filter="{mime: 'audio/'}" />
  </Teleport>

  <Teleport to="body">
    <FileUrlDialog v-model="vurls" @add="select($event); vurls = false" mime="audio/" />
  </Teleport>
</template>

<style scoped>
</style>
