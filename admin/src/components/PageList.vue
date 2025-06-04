<script>
  import gql from 'graphql-tag'
  import User from './User.vue'
  import Navigation from './Navigation.vue'
  import PageListItems from './PageListItems.vue'

  export default {
    components: {
      PageListItems,
      Navigation,
      User
    },

    props: {
      'nav': {type: Boolean, default: false}
    },

    emits: ['update:nav', 'update:item']
  }
</script>

<template>
  <v-app-bar :elevation="1" density="compact">
    <template #prepend>
      <v-btn @click.stop="$emit('update:nav', !nav)">
        <v-icon size="x-large">
          {{ `mdi-${nav ? 'close' : 'menu'}` }}
        </v-icon>
      </v-btn>
    </template>

    <v-app-bar-title>Pages</v-app-bar-title>

    <template #append>
      <User />
    </template>
  </v-app-bar>

  <Navigation :state="nav" @update:state="$emit('update:nav', $event)" />

  <v-main class="page-list">
    <PageListItems @update:item="$emit('update:item', $event)" />
  </v-main>
</template>

<style scoped>
</style>
