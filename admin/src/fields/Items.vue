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

    emits: ['update:modelValue', 'addFile', 'removeFile'],

    data() {
      return {
        items: [],
        panel: [],
      }
    },

    methods: {
      add() {
        this.items.push({})
        this.panel.push(this.items.length - 1)
        this.$emit('update:modelValue', this.items)
      },


      change() {
        this.$emit('update:modelValue', this.items)
      },


      remove(idx) {
        this.items.splice(idx, 1)
        this.$emit('update:modelValue', this.items)
      },


      title(item) {
        return Object.values(item || {})
          .map(v => v && typeof v !== 'object' && typeof v !== 'boolean' ? v : null)
          .filter(v => !!v)
          .join(' - ')
          .substring(0, 50) || ''
      },
    },

    watch: {
      modelValue: {
        immediate: true,
        handler(list) {
          this.items = list
        }
      }
    }
  }
</script>

<template>
  <v-expansion-panels class="items" v-model="panel" elevation="0" multiple>
    <VueDraggable v-model="items" @change="change()" draggable=".item" group="items" animation="500">

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
              @addFile="$emit('addFile', $event)"
              @removeFile="$emit('removeFile', $event)"
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
