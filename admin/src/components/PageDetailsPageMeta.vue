<script>
  import Fields from './Fields.vue'
  import Elements from './Elements.vue'
  import { useSchemaStore } from '../stores'

  export default {
    components: {
      Elements,
      Fields,
    },

    props: {
      'item': {type: Object, required: true},
      'files': {type: Object, default: () => {}},
    },

    emits: ['change', 'error'],

    data: () => ({
      vschemas: false,
      panel: []
    }),

    setup() {
      const schemas = useSchemaStore()
      return { schemas }
    },

    computed: {
      available() {
        return Object.keys(this.schemas).length
      }
    },

    methods: {
      add(type) {
        if(!this.item.meta) {
          this.item.meta = {}
        }

        if(this.item.meta[type]) {
          alert('Element is already available')
          return
        }

        this.item.meta[type] = {type: type, data: {}}
        this.panel.push(Object.keys(this.item.meta).length - 1)
        this.vschemas = false
      },


      error(el, value) {
        el._error = value
        this.$emit('error', Object.values(this.item.meta || {}).some(item => item._error))
      },


      fields(type) {
        if(!this.schemas.meta[type]?.fields) {
          console.warn(`No definition of fields for "${type}" available`)
          return []
        }

        return this.schemas.meta[type]?.fields
      },


      remove(code) {
        delete this.item.meta[code]
      },


      reset() {
        Object.values(this.item.meta || {}).forEach(el => {
          delete el._changed
          delete el._error
        })
      },


      title(el) {
        return Object.values(el.data || {})
          .map(v => v && typeof v !== 'object' && typeof v !== 'boolean' ? v : null)
          .filter(v => !!v)
          .join(' - ')
          .substring(0, 50) || el.label || ''
      },


      update(el) {
        el._changed = true
        this.$emit('change', true)
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
  <v-expansion-panels class="list" v-model="panel" elevation="0" multiple>

    <v-expansion-panel v-for="(el, code) in item.meta || {}" :key="code" :class="{changed: el._changed, error: el._error}">
      <v-expansion-panel-title expand-icon="mdi-pencil">
        <v-btn icon="mdi-delete" variant="text" @click="remove(code)"></v-btn>
        <div class="element-title">{{ title(el) }}</div>
        <div class="element-type">{{ el.type }}</div>
      </v-expansion-panel-title>
      <v-expansion-panel-text>

        <Fields ref="field"
          v-model:data="el.data"
          :fields="fields(el.type)"
          :files="files"
          @update:assets="el.files = $event"
          @error="error(el, $event)"
          @change="update(el)"
        />

      </v-expansion-panel-text>
    </v-expansion-panel>

  </v-expansion-panels>

  <div v-if="available" class="btn-group">
    <v-btn icon="mdi-view-grid-plus" color="primary" @click="vschemas = true" elevation="0"></v-btn>
  </div>

  <Teleport to="body">
    <v-dialog v-model="vschemas" scrollable width="auto">
      <Elements type="meta" @add="add($event)" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
.v-expansion-panel {
  border-inline-start: 3px solid transparent;
}

.v-expansion-panel.changed {
  border-inline-start: 3px solid rgb(var(--v-theme-warning));
}

.v-expansion-panel.error .v-expansion-panel-title {
  color: rgb(var(--v-theme-error));
}
</style>
