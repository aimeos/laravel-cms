<script>
  import { useDrawerStore } from '../stores'

  export default {
    props: {
      'content': {type: Array, required: true},
      'filter': {type: Object, required: true},
    },

    emits: ['update:filter'],

    setup() {
      const drawer = useDrawerStore()
      return { drawer }
    },
  }
</script>

<template>
  <v-navigation-drawer v-model="drawer.aside" width="224" mobile-breakpoint="md" location="end">

    <v-list v-for="(group, index) in content" :key="index" density="compact">
      <v-list-item v-for="(item, idx) in (group.items || [])" @click="$emit('update:filter', item.value)" rounded="lg">
        {{ item.title }}
      </v-list-item>
    </v-list>

  </v-navigation-drawer>
</template>

<style scoped>
  .title {
    padding: 10px 16px;
    font-size: 1.25rem;
    background-color: rgb(var(--v-theme-surface-light));
  }

  .v-list:not(:last-child) {
    border-bottom: 1px solid rgba(var(--v-theme-on-surface), var(--v-high-emphasis-opacity));
  }
</style>
