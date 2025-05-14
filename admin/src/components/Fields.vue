<script>
  export default {
    props: {
      'data': {type: Object, default: () => {}},
      'files': {type: Object, default: () => {}},
      'fields': {type: Object, required: true},
      'readonly': {type: Boolean, default: false},
    },

    emits: ['change', 'error', 'update:assets'],

    data() {
      return {
        assets: [],
        errors: {},
      }
    },

    methods: {
      addFile(id) {
        const ids = Array.isArray(id) ? id : [id]

        this.assets.push(...ids)
        this.$emit('update:assets', this.assets)
      },


      error(code, value) {
        this.errors[code] = value
        this.$emit('error', Object.values(this.errors).includes(true))
      },


      removeFile(id) {
        const ids = Array.isArray(id) ? id : [id]

        for(const id of ids) {
          const idx = this.assets.findIndex(fileid => fileid === id)

          if(idx !== -1) {
            this.assets.splice(idx, 1)
          }
        }

        this.$emit('update:assets', this.assets)
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
    <v-label>{{ field.label || code }}</v-label>
    <component ref="field"
      :is="field.type?.charAt(0)?.toUpperCase() + field.type?.slice(1)"
      :assets="files"
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
    margin: 1.5rem 0;
    padding-inline-start: 1rem;
    border-inline-start: 3px solid #D0D8E0;
  }

  .item.error {
    border-inline-start: 3px solid rgb(var(--v-theme-error));
  }

  label {
    font-weight: bold;
    text-transform: capitalize;
    margin-bottom: 0.5rem;
  }
</style>