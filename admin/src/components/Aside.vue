<script>
  import { useSideStore } from '../stores'

  export default {
    setup() {
      const aside = useSideStore()
      return { aside }
    },
    props: ['state'],
    emits: ['update:state'],
    data: () => ({
      panel: {},
      width: window.innerWidth
    }),
    methods: {
      toggle(key, code) {
        if(!this.aside.used[key]) {
          this.aside.used[key] = {}
        }
        this.aside.used[key][code] = !this.aside.used[key][code]
      }
    }
  }
</script>

<template>
  <v-navigation-drawer location="end" width="220" :modelValue="state" @update:modelValue="$emit('update:state', $event)" :rail="width > 1200 ? false : true" expand-on-hover>

    <v-expansion-panels v-for="(items, key) in aside.store" :key="key" v-model="panel[key]">
      <v-expansion-panel elevation="0">
        <v-expansion-panel-title>
          {{ key }}
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <v-list density="compact">
            <v-list-item v-for="(name, code) in items" :key="code" :value="code" @click="toggle(key, code)">
              {{ name }}
            </v-list-item>
          </v-list>
        </v-expansion-panel-text>
      </v-expansion-panel>
    </v-expansion-panels>

  </v-navigation-drawer>
</template>

<style scoped>
  .v-navigation-drawer--right .v-expansion-panel--active>.v-expansion-panel-title {
    min-height: unset;
  }

  .v-navigation-drawer--right .v-expansion-panel {
    background-color: inherit;
  }
</style>
