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

    setup() {
      const elements = useElementStore()
      return { elements }
    },

    data: () => ({
      velements: false,
      panel: []
    }),

    computed: {
      available() {
        return Object.keys(this.elements).length
      }
    },

    methods: {
      add(type) {
        if(!this.item.data.meta) {
          this.item.data.meta = {}
        }

        if(this.item.data.meta[type]) {
          alert('Element is already available')
          return
        }

        this.item.data.meta[type] = {type: type, data: {}, files: []}
        this.panel.push(Object.keys(this.item.data.meta).length - 1)
        this.velements = false
      },

      remove(code) {
        delete this.item.data.meta[code]
      },

      title(el) {
        return Object.values(el.data || {}).filter(v => typeof v !== 'object' && !!v).join(' - ').substring(0, 50) || el.label || ''
      }
    }
  }
</script>

<template>
  <v-expansion-panels class="list" v-model="panel" elevation="0" multiple>

    <v-expansion-panel v-for="(el, code) in item.data?.meta || {}" :key="code">
      <v-expansion-panel-title expand-icon="mdi-pencil">
        <v-btn icon="mdi-delete" variant="text" @click="remove(code)"></v-btn>
        <div class="element-title">{{ title(el) }}</div>
        <div class="element-type">{{ el.type }}</div>
      </v-expansion-panel-title>
      <v-expansion-panel-text>
        <Fields :fields="elements.meta[code]?.fields || []" v-model:data="el.data" v-model:assets="el.files" />
      </v-expansion-panel-text>
    </v-expansion-panel>

  </v-expansion-panels>

  <div v-if="available" class="btn-group">
    <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true" elevation="0"></v-btn>
  </div>

  <Teleport to="body">
    <v-dialog v-model="velements" scrollable width="auto">
      <Elements type="meta" @add="add($event)" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
</style>
