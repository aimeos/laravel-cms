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
          image.src = this.url(Object.values(data.previews)[0] || data.path)
        }).then(() => {
          return File.methods.handle.call(this, data, path)
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
        <div v-if="file.path" class="file">
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
          <button class="delete" v-if="!readonly && file.path" @click="remove()"
            title="Remove image"
            type="button">
            <v-icon icon="mdi-trash-can" role="img"></v-icon>
          </button>
        </div>
        <div v-else class="file file-input">
          <input type="file"
            @input="add($event)"
            :accept="config.accept || 'image/*'"
            :id="'image-' + index"
            :disabled="readonly"
            :value="selected"
            hidden>
          <label :for="'image-' + index">Add image</label>
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
  .v-responsive.v-img, img {
    max-width: 100%;
    height: 200px;
  }
</style>
