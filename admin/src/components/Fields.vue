<script>
  import gql from 'graphql-tag'
  import { useAppStore } from '../stores'
  import { VueDraggable } from 'vue-draggable-plus'

  export default {
    components: {
      VueDraggable
    },
    props: ['data', 'fields'],
    emits: ['update:data'],
    data: () => ({
    }),
    setup() {
      const app = useAppStore()
      return { app }
    },
    methods: {
      addListItem(code) {
        if(!this.data[code]) {
          this.data[code] = []
        }
        this.data[code].push('')
      },


      removeListItem(code, idx) {
        if(typeof this.data[code][idx] !== 'undefined') {
          this.data[code].splice(idx, 1)
        }
        return false
      },


      file(code, ev) {
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        this.data[code] = {path: URL.createObjectURL(files[0]), uploading: true}

        this.upload(files[0]).then(data => {
          URL.revokeObjectURL(this.data[code].path)
          this.data[code] = data
        }).catch(error => {
          delete this.data[code]
          console.error(`addFile(` + code + `)`, error)
        })
      },


      files(code, ev) {
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        if(!this.data[code]) {
          this.data[code] = []
        }

        for(let i = 0; i < files.length; i++) {
          const idx = this.data[code].length
          this.data[code][idx] = {path: URL.createObjectURL(files[i]), uploading: true}

          this.upload(files[i]).then(data => {
            URL.revokeObjectURL(this.data[code][idx].path)
            this.data[code][idx] = data
          }).catch(error => {
            delete this.data[code][idx]
            console.error(`addFile(` + code + `)`, error)
          })
        }
      },

      async remove(code, idx = null) {
        const entry = idx !== null ? this.data[code][idx] : this.data[code]

        await this.$apollo.mutate({
          mutation: gql`mutation($id: ID!) {
            dropFile(id: $id) {
              id
            }
          }`,
          variables: {
            id: entry.id
          }
        }).then(response => {
          if(response.errors) {
            throw response.errors
          }
          if(idx !== null) {
            this.data[code].splice(idx, 1)
          } else {
            delete this.data[code]
          }
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


      async upload(file) {
        return await this.$apollo.mutate({
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
          return data
        })
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
  <v-container>
    <v-form @submit.prevent>

      <v-row v-for="(field, code) in fields" :key="code">

        <v-col cols="12" sm="3" class="form-label">
          <v-label>{{ field.label || code }}</v-label>
        </v-col>

        <v-col cols="12" sm="9">

          <v-autocomplete v-if="field.type === 'autocomplete'"
            :items="field.options || []"
            :label="field.label || ''"
            v-model="data[code]"
            density="comfortable"
            variant="underlined"
          ></v-autocomplete>

          <v-checkbox v-if="field.type === 'checkbox'"
            :label="field.label || ''"
            v-model="data[code]"
          ></v-checkbox>

          <v-combobox v-if="field.type === 'combobox'"
            :items="field.options || []"
            :label="field.label || ''"
            :multiple="field.multiple"
            :chips="field.multiple"
            v-model="data[code]"
            density="comfortable"
            variant="underlined"
            clearable
          ></v-combobox>

          <v-file-input v-if="field.type === 'file'"
            :label="field.label || ''"
            v-model="data[code]"
            density="comfortable"
            variant="underlined"
            clearable
          ></v-file-input>

          <div v-if="field.type === 'image'">
            <div v-if="data[code]" class="image">
              <v-progress-linear v-if="data[code].uploading"
                color="primary"
                height="5"
                indeterminate
                rounded
              ></v-progress-linear>
              <v-img
                :draggable="false"
                :src="url(data[code].path)"
                :srcset="srcset(data[code].previews)"
              ></v-img>
              <button v-if="data[code].id" @click="remove(code)"
                title="Remove image"
                type="button">
                <v-icon icon="mdi-trash-can" role="img"></v-icon>
              </button>
            </div>
            <div v-else class="image file-input">
              <input type="file"
                @input="file(code, $event)"
                :id="code + '-images-1'"
                :value="null"
                accept="image/*"
                hidden>
              <label :for="code + '-images-1'">Add file</label>
            </div>
          </div>

          <VueDraggable v-if="field.type === 'images'" v-model="data[code]" draggable=".image" group="images" class="images">
            <div v-for="(item, idx) in (data[code] || [])" :key="idx" class="image">
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
              <button v-if="item.id" @click="remove(code, idx)"
                title="Remove image"
                type="button">
                <v-icon icon="mdi-trash-can" role="img"></v-icon>
              </button>
            </div>
            <div class="file-input">
              <input type="file"
                @input="files(code, $event)"
                :id="code + '-images-1'"
                :value="null"
                accept="image/*"
                multiple
                hidden>
              <label :for="code + '-images-1'">Add files</label>
            </div>
          </VueDraggable>

          <div v-if="field.type === 'list'" class="list">
            <VueDraggable v-model="data[code]" group="list">
              <v-text-field v-for="(item, idx) in (data[code] || [])" :key="idx"
                @click:append="removeListItem(code, idx)"
                :label="field.label || ''"
                v-model="data[code][idx]"
                density="comfortable"
                variant="underlined"
                append-icon="mdi-trash-can"
              ></v-text-field>
            </VueDraggable>

            <button
              @click="addListItem(code)"
              title="Add item"
              type="button">
              <v-icon icon="mdi-plus-box" role="img"></v-icon>
            </button>
          </div>

          <v-number-input v-if="field.type === 'number'"
            :label="field.label || ''"
            v-model="data[code]"
            density="comfortable"
            variant="outlined"
          ></v-number-input>

          <v-radio-group v-if="field.type === 'radio'"
            v-model="data[code]">
            <v-radio v-for="(label, value) in (field.options || [])"
              :label="label"
              :value="value">
            </v-radio>
          </v-radio-group>

          <v-range-slider v-if="field.type === 'range'"
            v-model="data[code]"
          ></v-range-slider>

          <v-select v-if="field.type === 'select'"
            :items="field.options || []"
            :label="field.label || ''"
            :multiple="field.multiple"
            :chips="field.multiple"
            v-model="data[code]"
            density="comfortable"
            variant="underlined"
            item-title="label"
          ></v-select>

          <v-slider v-if="field.type === 'slider'"
            v-model="data[code]"
          ></v-slider>

          <v-switch v-if="field.type === 'switch'"
            :label="field.label || ''"
            v-model="data[code]"
            inset
          ></v-switch>

          <v-text-field v-if="field.type === 'string'"
            :label="field.label || ''"
            v-model="data[code]"
            density="comfortable"
            variant="underlined"
            clearable
          ></v-text-field>

          <v-textarea v-if="field.type === 'table'"
            placeholder="val;val;val
val;val;val"
            :auto-grow="true"
            v-model="data[code]"
            variant="underlined"
            density="comfortable"
            clearable
          ></v-textarea>

          <v-textarea v-if="field.type === 'text'"
            :label="field.label || ''"
            :auto-grow="true"
            :counter="true"
            v-model="data[code]"
            variant="underlined"
            density="comfortable"
            clearable
          ></v-textarea>

          <v-date-picker v-if="field.type === 'date'"
            v-model="data[code]"
            show-adjacent-months
          ></v-date-picker>

          <v-color-picker v-if="field.type === 'color'"
            v-model="data[code]"
          ></v-color-picker>

        </v-col>

      </v-row>

    </v-form>
  </v-container>
</template>

<style scoped>
  .v-form label {
    font-weight: bold;
    text-transform: capitalize;
  }

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
</style>
