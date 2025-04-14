<script>
  import Fields from './Fields.vue'
  import Elements from './Elements.vue'
  import { useElementStore } from '../stores'

  export default {
    components: {
      Elements,
      Fields,
    },
    props: ['item'],
    emits: ['update:item'],
    setup() {
      const elements = useElementStore()
      return { elements }
    },
    data: () => ({
      meta: {},
      velements: false,
      panel: []
    }),
    mounted() {
      this.meta = JSON.parse(this.item.versions[0]?.data?.meta || this.item.meta || '{}')
    },
    computed: {
      available() {
        return Object.keys(this.elements).length
      }
    },
    methods: {
      add(item) {
        if(this.meta[item.type]) {
          return
        }

        this.meta[item.type] = Object.assign(item, {data: {}})
        this.panel.push(Object.keys(this.meta).length - 1)
        this.velements = false
      },

      remove(code) {
        delete this.meta[code]
      },

      title(el) {
        return Object.values(el.data || {}).filter(v => typeof v !== 'object' && !!v).join(' - ').substring(0, 50) || el.label || ''
      }
    },
    watch: {
      meta: {
        deep: true,
        handler() {
          const item = {...this.item}
          item.meta = this.meta
          this.$emit('update:item', item)
        }
      }
    }
  }
</script>

<template>
  <v-expansion-panels class="list" v-model="panel" multiple>

    <v-expansion-panel v-for="(el, code) in meta || {}" :key="code" elevation="1" rounded="lg">
      <v-expansion-panel-title expand-icon="mdi-pencil">
        <v-btn icon="mdi-delete" variant="text" @click="remove(code)"></v-btn>
        <div class="element-title">{{ title(el) }}</div>
        <div class="element-type">{{ el.type }}</div>
      </v-expansion-panel-title>
      <v-expansion-panel-text>
        <Fields :fields="el.fields" v-model:data="el.data" v-model:assets="el.files" />
      </v-expansion-panel-text>
    </v-expansion-panel>

  </v-expansion-panels>

  <div v-if="available" class="btn-group">
    <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true"></v-btn>
  </div>

  <Teleport to="body">
    <v-dialog v-model="velements" scrollable width="auto">
      <Elements type="meta" @add="add($event)" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
</style>
