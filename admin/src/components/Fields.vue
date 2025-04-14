<script>
  export default {
    props: {
      'data': {type: Object, default: () => {}},
      'assets': {type: Array, default: () => []},
      'fields': {type: Object, required: true},
    },
    emits: ['update:data', 'update:assets'],
    data() {
      return {
        files: [...this.assets],
      }
    },
    methods: {
      addAsset(file) {
        this.files.push(file)
        this.$emit('update:assets', this.files)
      },

      removeAsset(id) {
        const idx = this.files.findIndex(item => item.id === id)

        if(idx !== -1) {
          this.files.splice(idx, 1)
          this.$emit('update:assets', this.files)
        }
      }
    }
  }
</script>

<template>
  <v-container>
    <v-row v-for="(field, code) in fields" :key="code">
      <v-col cols="12" sm="3" class="form-label">
        <v-label>{{ field.label || code }}</v-label>
      </v-col>
      <v-col cols="12" sm="9">
        <component :is="field.type?.charAt(0)?.toUpperCase() + field.type?.slice(1)"
          v-model="data[code]"
          :config="field"
          :assets="assets"
          @addAsset="addAsset($event)"
          @removeAsset="removeAsset($event)"
        ></component>
      </v-col>
    </v-row>
  </v-container>
</template>
