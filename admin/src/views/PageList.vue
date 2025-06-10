<script>
  import gql from 'graphql-tag'
  import User from '../components/User.vue'
  import AsideList from '../components/AsideList.vue'
  import Navigation from '../components/Navigation.vue'
  import PageListItems from '../components/PageListItems.vue'
  import { useAuthStore, useDrawerStore } from '../stores'
  import PageDetail from '../views//PageDetail.vue'

  export default {
    components: {
      PageListItems,
      PageDetail,
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
    },

    methods: {
      open(item) {
        this.openView(PageDetail, {item: item})
      }
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
    <PageListItems @select="open($event)" :filter="filter" />
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
