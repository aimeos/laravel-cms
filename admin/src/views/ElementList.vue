<script>
  import gql from 'graphql-tag'
  import User from '../components/User.vue'
  import AsideList from '../components/AsideList.vue'
  import Navigation from '../components/Navigation.vue'
  import ElementDetail from '../views/ElementDetail.vue'
  import ElementListItems from '../components/ElementListItems.vue'
  import { useAuthStore, useDrawerStore } from '../stores'

  export default {
    components: {
      ElementListItems,
      ElementDetail,
      Navigation,
      AsideList,
      User
    },

    inject: ['openView'],

    data: () => ({
      aside: null,
      filter: {'trashed': 'WITHOUT'},
    }),

    setup() {
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer }
    },

    methods: {
      open(item) {
        this.openView(ElementDetail, {item: item})
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

    <v-app-bar-title>Shared elements</v-app-bar-title>

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

  <v-main class="element-list">
    <ElementListItems :filter="filter" @select="open($event)" />
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
