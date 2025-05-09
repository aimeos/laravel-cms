<script>
  export default {
    props: {
      'data': {type: Object, default: () => {}},
      'assets': {type: Array, default: () => []},
      'fields': {type: Object, required: true},
    },

    emits: ['change', 'update:assets'],

    data() {
      return {
        files: this.assets,
      }
    },

    methods: {
      addFile(file) {
        const idx = this.files.findIndex(item => item.id === file.id)

        if(idx !== -1) {
          this.files[idx] = file
        } else {
          this.files.push(file)
        }
        this.$emit('update:assets', this.files)
      },


      validate() {
        const list = []

        for(const cmp of (this.$refs.field || [])) {
          list.push(cmp.validate())
        }

        return Promise.all(list)
      },


      removeFile(id) {
        const idx = this.files.findIndex(item => item.id === id)

        if(idx !== -1) {
          this.files.splice(idx, 1)
          this.$emit('update:assets', this.files)
        }
      },


      update(code, value) {
        this.data[code] = value
        this.$emit('change', this.data[code])
      },
    }
  }
</script>

<template>
  <div v-for="(field, code) in fields" :key="code" class="item">
    <v-label>{{ field.label || code }}</v-label>
    <component ref="field"
      :is="field.type?.charAt(0)?.toUpperCase() + field.type?.slice(1)"
      :config="field"
      :assets="assets"
      :modelValue="data[code]"
      @addFile="addFile($event)"
      @removeFile="removeFile($event)"
      @update:modelValue="update(code, $event)"
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