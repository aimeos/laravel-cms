<script>
  import gql from 'graphql-tag'
  import { VueDraggable } from 'vue-draggable-plus'

  export default {
    components: {
      VueDraggable
    },
    props: {
      'modelValue': {type: Array, default: () => []},
      'config': {type: Object, default: () => {}},
      'assets': {type: Array, default: () => []},
    },
    emits: ['update:modelValue', 'addAsset', 'removeAsset'],
    data() {
      return {
        items: [...this.modelValue],
        panel: [],
      }
    },
    methods: {
      add() {
        this.items.push({})
        this.panel.push(this.items.length - 1)
      },

      remove(idx) {
        this.items.splice(idx, 1)
      },

      title(item) {
        return Object.values(item).filter(v => typeof v !== 'object' && !!v).join(' - ').substring(0, 50) || ''
      },
    },
    watch: {
      items: {
        deep: true,
        handler() {
          this.$emit('update:modelValue', this.items)
        }
      }
    }
  }
</script>

<template>
  <v-expansion-panels class="items" v-model="panel" elevation="0" multiple>
    <VueDraggable v-model="items" draggable=".item" group="items">

      <v-expansion-panel v-for="(item, idx) in items" :key="idx" class="item">
        <v-expansion-panel-title>
          <v-btn icon="mdi-trash-can" variant="plain" @click="remove(idx)"></v-btn>
          <div class="element-title">{{ title(item) }}</div>
        </v-expansion-panel-title>

        <v-expansion-panel-text>
          <div v-for="(field, code) in (config.item || {})" :key="code" class="field">
            <v-label>{{ field.label || code }}</v-label>
            <component :is="field.type?.charAt(0)?.toUpperCase() + field.type?.slice(1)"
              v-model="items[idx][code]"
              :config="field"
              :assets="assets"
              @addAsset="$emit('addAsset', $event)"
              @removeAsset="$emit('removeAsset', $event)"
            ></component>
          </div>
        </v-expansion-panel-text>
      </v-expansion-panel>

    </VueDraggable>
  </v-expansion-panels>

  <div class="btn-group">
    <v-btn icon="mdi-view-grid-plus" @click="add()"></v-btn>
  </div>
</template>

<style scoped>
  .v-expansion-panel.v-expansion-panel--active.item {
    border: 1px solid #D0D8E0;
  }

  .items.v-expansion-panels {
    display: block;
  }

  .field {
    margin: 1.5rem 0;
  }

  label {
    font-weight: bold;
    text-transform: capitalize;
    margin-bottom: 0.5rem;
  }
</style>
