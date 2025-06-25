<script>
  import gql from 'graphql-tag'
  import { useMessageStore } from '../stores'

  export default {
    props: {
      'data': {type: Object, default: () => {}},
      'files': {type: Array, default: () => []},
      'assets': {type: Object, default: () => {}},
      'readonly': {type: Boolean, default: false},
      'fields': {type: Object, required: true},
      'lang': {type: [String, null], default: null},
    },

    emits: ['change', 'error', 'update:files'],

    data() {
      return {
        composing: {},
        errors: {},
      }
    },

    setup() {
      const messages = useMessageStore()
      return { messages }
    },

    methods: {
      addFile(id) {
        const ids = Array.isArray(id) ? id : [id]
        const files = [...this.files]

        files.push(...ids)
        this.$emit('update:files', files)
      },


      compose(code) {
        const info = Object.entries(this.data).map(([key, value]) => {
          return key !== code && typeof value === 'string' || value instanceof String ? key + '="' + value.trim() + '"' : null
        }).filter(v => !!v).join(' | ')

        const context = [
          'label: ' + (this.fields[code].label || code),
          'required output format: ' + this.fields[code].type,
          'required output language: ' + (this.lang || 'en'),
          this.fields[code].min ? 'minimum characters: ' + this.fields[code].min : null,
          this.fields[code].max ? 'maximum characters: ' + this.fields[code].max : null,
          this.fields[code].placeholder ? 'hint text: ' + this.fields[code].placeholder : null,
          'contextual information: ' + info,
        ]

        this.composing[code] = true

        this.$apollo.mutate({
          mutation: gql`mutation($prompt: String!, $lang: String!, $context: String) {
            compose(prompt: $prompt, lang: $lang, context: $context)
          }`,
          variables: {
            prompt: this.data[code],
            context: context.filter(v => !!v).join(', '),
            lang: this.lang || 'en',
          }
        }).then(result => {
          if(result.errors) {
            throw result
          }

          this.update(code, result.data?.compose)
        }).catch(error => {
          this.messages.add('Error composing text', 'error')
          this.$log(`Fields::compose(): Error composing text`, code, error)
        }).finally(() => {
          this.composing[code] = false
        })
      },


      error(code, value) {
        this.errors[code] = value
        this.$emit('error', Object.values(this.errors).includes(true))
      },


      removeFile(id) {
        const ids = Array.isArray(id) ? id : [id]
        const files = [...this.files]

        for(const id of ids) {
          const idx = files.findIndex(fileid => fileid === id)

          if(idx !== -1) {
            files.splice(idx, 1)
          }
        }

        this.$emit('update:files', files)
      },


      update(code, value) {
        this.data[code] = value
        this.$emit('change', this.data[code])
      },


      validate() {
        const list = []

        this.$refs.field?.forEach(field => {
          list.push(field.validate())
        })

        return Promise.all(list).then(result => {
          return result.every(r => r)
        });
      }
    }
  }
</script>

<template>
  <div v-for="(field, code) in fields" :key="code" class="item" :class="{error: errors[code]}">
    <v-label>
      {{ field.label || code }}
      <v-btn :loading="composing[code]" icon="mdi-creation" variant="flat" @click="compose(code)" />
    </v-label>
    <component ref="field"
      :is="field.type?.charAt(0)?.toUpperCase() + field.type?.slice(1)"
      :assets="assets"
      :config="field"
      :readonly="readonly"
      :modelValue="data[code]"
      @addFile="addFile($event)"
      @removeFile="removeFile($event)"
      @update:modelValue="update(code, $event)"
      @error="error(code, $event)"
    ></component>
  </div>
</template>

<style scoped>
  .item {
    margin: 24px 0;
    padding-inline-start: 8px;
    border-inline-start: 3px solid #D0D8E0;
  }

  .item.error {
    border-inline-start: 3px solid rgb(var(--v-theme-error));
  }

  .v-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
  }

  label {
    font-weight: bold;
    text-transform: capitalize;
    margin-bottom: 4px;
  }
</style>