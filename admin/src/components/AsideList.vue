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

    data() {
      return {
        open: [0, 1, 2],
      }
    },
  }
</script>

<template>
  <v-navigation-drawer v-model="drawer.aside" mobile-breakpoint="md" location="end">

    <v-list v-model:opened="open">
      <v-list-group v-for="(group, index) in content" :key="index" :value="index">
        <template v-slot:activator="{ props }">
          <v-list-item v-bind="props">{{ group.name }}</v-list-item>
        </template>

        <v-list-item
          v-for="(item, idx) in (group.items || [])"
          @click="$emit('update:filter', item.value)"
          :active="JSON.stringify(item.value) === JSON.stringify(filter)"
          :key="idx"
          rounded="lg"
        >
          <v-icon v-if="item.icon" :icon="item.icon" class="icon"></v-icon>
          {{ item.title }}
        </v-list-item>

      </v-list-group>
    </v-list>

  </v-navigation-drawer>
</template>

<style scoped>
  .v-navigation-drawer {
    border-top-left-radius: 8px;
  }

  .v-locale--is-rtl .v-navigation-drawer {
    border-top-left-radius: 0;
    border-top-right-radius: 8px;
  }

  .v-list-item {
    text-transform: capitalize;
  }

  .v-list-item .icon {
    margin-inline-end: 8px;
    font-size: 100%;
  }
</style>
