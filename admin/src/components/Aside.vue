<script>
  import { useSideStore } from '../stores'

  export default {
    props: {
      'state': {type: [Boolean, null], required: true}
    },

    emits: ['update:state'],

    data: () => ({
      active: {},
      panel: [0, 1, 2, 3, 4, 5, 6, 7, 8, 9],
      width: window.innerWidth
    }),

    setup() {
      const sidestore = useSideStore()
      return { sidestore }
    },

    methods: {
      isActive(key, code) {
        if(typeof this.active[key] === 'undefined') {
          this.active[key] = {}
        }

        if(typeof this.active[key][code] === 'undefined') {
          this.active[key][code] = true
        }

        return this.active[key][code]
      },

      toggle(key, code) {
        this.sidestore.toggle(key, code)
        this.active[key][code] = !this.active[key][code]
      }
    }
  }
</script>

<template>
  <v-navigation-drawer location="end" rail-width="220" :modelValue="state" @update:modelValue="$emit('update:state', $event)" :rail="width > 1200 ? false : true" expand-on-hover>

    <v-expansion-panels variant="accordion" v-model="panel">
      <v-expansion-panel v-for="(items, key) in sidestore.store" :key="key" v-show="Object.keys(sidestore.store[key]).length" elevation="0">
        <v-expansion-panel-title>
          {{ key }}
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <v-list density="compact">
            <v-list-item v-for="(count, code) in items" :key="code" :active="isActive(key, code)" @click="toggle(key, code)">
              {{ code }} ({{ count }})
            </v-list-item>
          </v-list>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

  </v-navigation-drawer>
</template>

<style>
  .v-navigation-drawer--right .v-expansion-panel--active>.v-expansion-panel-title {
    min-height: unset;
  }

  .v-navigation-drawer--right .v-list-item__content {
    color: rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
  }

  .v-expansion-panel-text__wrapper {
    padding: 0 16px 12px;
  }

  .v-list {
    padding: 0;
  }
</style>
