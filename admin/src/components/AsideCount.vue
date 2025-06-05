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

    computed: {
      stores() {
        const keys = Object.keys(this.sidestore.store).sort()
        const map = {}

        for(const key of keys) {
          map[key] = this.sidestore.store[key]
        }

        return map
      }
    },

    methods: {
      isActive(key, code) {
        if(typeof this.active[key] === 'undefined') {
          this.active[key] = {}
        }

        if(typeof this.active[key][code] === 'undefined') {
          this.active[key][code] = false
        }

        return this.active[key][code]
      },


      sort(items) {
        const keys = Object.keys(items).sort()
        const map = {}

        for(const key of keys) {
          map[key] = items[key]
        }

        return map
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

    <v-expansion-panels variant="accordion" v-model="panel" multiple>
      <v-expansion-panel v-for="(items, key) in stores" :key="key" v-show="Object.keys(items).length" :class="key" elevation="0">
        <v-expansion-panel-title>
          {{ key }}
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <v-list density="compact">
            <v-list-item v-for="(value, code) in items" :key="code" :active="isActive(key, code)" @click="toggle(key, code)" rounded="lg">
              <span class="name">{{ code }}</span>
              <span class="value">{{ value }}</span>
            </v-list-item>
          </v-list>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

  </v-navigation-drawer>
</template>

<style scoped>
  .v-expansion-panel-title {
    text-transform: capitalize;
    min-height: 48px !important;
  }

  .v-list {
    padding: 0;
  }

  .v-list-item {
    margin-bottom: 0.25rem;
  }

  .v-list-item .value::before {
    content: ' (';
  }

  .v-list-item .value::after {
    content: ')';
  }
</style>
