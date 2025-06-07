<script>
  import gql from 'graphql-tag'
  import User from './User.vue'
  import AsideList from './AsideList.vue'
  import Navigation from './Navigation.vue'
  import PageListItems from './PageListItems.vue'
  import { useAuthStore, useDrawerStore } from '../stores'

  export default {
    components: {
      PageListItems,
      Navigation,
      AsideList,
      User
    },

    data: () => ({
      filter: {'trashed': 'WITHOUT'},
    }),

    setup() {
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer }
    }
  }
</script>

<template>
  <v-app-bar :elevation="1" density="compact">
    <template #prepend>
      <v-btn @click="drawer.toggle('nav')">
        <v-icon size="x-large">
          {{ drawer.nav ? 'mdi-close' : 'mdi-menu' }}
        </v-icon>
      </v-btn>
    </template>

    <v-app-bar-title>Pages</v-app-bar-title>

    <template #append>
      <User />

      <v-btn @click="drawer.toggle('aside')">
        <v-icon size="x-large">
          {{ drawer.aside ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <Navigation />

  <v-main class="page-list">
    <PageListItems @update:item="$emit('update:item', $event)" :filter="filter" />
  </v-main>

  <AsideList v-model:filter="filter" :content="[{
      group: 'trashed',
      items: [
        { title: 'Non-trashed', value: {'trashed': 'WITHOUT'} },
        { title: 'Include trashed', value: {'trashed': 'WITH'} },
        { title: 'Only trashed', value: {'trashed': 'ONLY'} }
      ]
    }, {
      group: 'editor',
      items: [
        { title: 'Edited by me', value: {'editor': this.auth.me.email} },
      ]
    }]"
  />
</template>

<style scoped>
</style>
