<script>
  import { useSchemaStore } from '../stores'

  export default {
    props: {
      'type': {type: String, required: true}
    },

    emits: ['add'],

    data: () => ({
      tab: ['basic'],
    }),

    setup() {
      const schemas = useSchemaStore()
      return { schemas }
    },

    computed: {
      groups() {
        const map = {}

        for(const el of Object.values(this.schemas[this.type] || {})) {
          const name = el.group || 'uncategorized'
          map[name] = map[name] || []
          map[name].push(el)
        }

        return map
      }
    },

    methods: {
      select(item) {
        this.$emit('add', {id: contentid(), type: item.type, data: {}})
      }
    }
  }
</script>

<template>
  <v-tabs v-model="tab">
    <v-tab v-for="(group, name) in groups" :key="name" :value="name">{{ name }}</v-tab>
  </v-tabs>

  <v-tabs-window v-model="tab">
    <v-tabs-window-item v-for="name in Object.keys(groups)" :key="name" :value="name">

      <v-card>
        <v-btn v-for="item in groups[name]" :key="item.type" variant="text" stacked
          @click="$emit('add', item)">
          <template v-slot:prepend>
            <span class="icon" v-html="item.icon"></span>
          </template>
          {{ item.type }}
        </v-btn>
      </v-card>

    </v-tabs-window-item>
  </v-tabs-window>
</template>

<style scoped>
  .v-container {
    background-color: rgb(var(--v-theme-surface));
    max-width: 100%;
    min-width: 50vw;
    padding: 1rem !important;
  }

  .v-tabs {
    background-color: rgb(var(--v-theme-background));
  }

  .v-card {
    padding: 1rem 0;
  }

  .v-btn {
    width: 10rem;
  }

  .icon {
    width: 2rem;
  }
</style>
