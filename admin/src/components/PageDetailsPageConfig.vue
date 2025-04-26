<script>
  import Fields from './Fields.vue'
  import Elements from './Elements.vue'
  import { useElementStore } from '../stores'

  export default {
    components: {
      Elements,
      Fields,
    },

    props: {
      'item': {type: Object, required: true}
    },

    data: () => ({
      velements: false,
      panel: [],
    }),

    setup() {
      const elements = useElementStore()
      return { elements }
    },

    computed: {
      available() {
        return Object.keys(this.elements).length
      }
    },

    methods: {
      add(type) {
        if(!this.item.config) {
          this.item.config = {}
        }

        if(this.item.config[type]) {
          alert('Element is already available')
          return
        }

        this.item.config[type] = {type: type, data: {}, files: []}
        this.panel.push(Object.keys(this.item.config).length - 1)
        this.velements = false
      },


      fields(type) {
        if(!this.elements.config[type]?.fields) {
          console.warn(`No definition of fields for "${type}" available`)
          return []
        }

        return this.elements.config[type]?.fields
      },


      remove(code) {
        delete this.item.config[code]
      },


      title(el) {
        return Object.values(el.data || {})
          .map(v => v && typeof v !== 'object' && typeof v !== 'boolean' ? v : null)
          .filter(v => !!v)
          .join(' - ')
          .substring(0, 50) || el.label || ''
      }
    }
  }
</script>

<template>
  <v-expansion-panels class="list" v-model="panel" elevation="0" multiple>

    <v-expansion-panel v-for="(el, code) in (item.config || {})" :key="code">
      <v-expansion-panel-title expand-icon="mdi-pencil">
        <v-btn icon="mdi-delete" variant="text" @click="remove(code)"></v-btn>
        <div class="element-title">{{ title(el) }}</div>
        <div class="element-type">{{ el.type }}</div>
      </v-expansion-panel-title>
      <v-expansion-panel-text>

        <Fields
          :fields="fields(el.type)"
          v-model:data="el.data"
          v-model:assets="el.files"
          @change="el._changed = true"
        />

      </v-expansion-panel-text>
    </v-expansion-panel>

  </v-expansion-panels>

  <div v-if="available" class="btn-group">
    <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true" elevation="0"></v-btn>
  </div>

  <Teleport to="body">
    <v-dialog v-model="velements" scrollable width="auto">
      <Elements type="config" @add="add($event)" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
</style>
