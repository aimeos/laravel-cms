<script>
  import Elements from './Elements.vue'
  import Fields from './Fields.vue'

  export default {
    components: {
      Elements,
      Fields,
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      panel: [],
      config: {},
      velements: false,
      elements: {
        'test': {
          type: 'test',
          label: 'Test string+color',
          fields: {
            'test/key': {type: 'string', label: 'Test string config'},
            'test/color': {type: 'color', label: 'Test color selector'}
          }
        },
        'test2': {
          type: 'test2',
          fields: {
            'test2/key': {type: 'string', label: 'Second string value'}
          }
        },
      }
    }),
    computed: {
      available() {
        return Object.keys(this.elements).length
      }
    },
    methods: {
      add(code) {
        if(!this.elements[code]) {
          console.error(`Element not found "${code}"`)
          return
        }

        if(this.config[code]) {
          return
        }

        this.config[code] = Object.assign(JSON.parse(JSON.stringify(this.elements[code])), {data: {}})
        this.panel.push(Object.keys(this.meta).length - 1)
        this.velements = false
      },

      remove(code) {
        delete this.config[code]
      },

      title(el) {
        return Object.values(el.data || {}).filter(v => typeof v !== 'object' && !!v).join(' - ').substring(0, 50) || el.label || ''
      },

      use(data) {
        this.config = data
        this.vhistory = null
      }
    },
  }
</script>

<template>
  <v-sheet>
    <v-expansion-panels class="list" v-model="panel" multiple>

      <v-expansion-panel v-for="(el, code) in config || {}" :key="code" elevation="1" rounded="lg">
        <v-expansion-panel-title expand-icon="mdi-pencil">
          <v-btn icon="mdi-delete" variant="text" @click="remove(code)"></v-btn>
          <div class="element-title">{{ title(el) }}</div>
          <div class="element-type">{{ el.type }}</div>
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <Fields :fields="el.fields" v-model:data="el.data" />
        </v-expansion-panel-text>
      </v-expansion-panel>

    </v-expansion-panels>

    <div v-if="available" class="btn-group">
      <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true"></v-btn>
    </div>
  </v-sheet>

  <Teleport to="body">
    <v-dialog v-model="velements" scrollable width="auto">
      <Elements :ce="elements" @add="add($event.type)" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
</style>
