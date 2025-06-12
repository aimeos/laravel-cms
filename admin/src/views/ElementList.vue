<script>
  import gql from 'graphql-tag'
  import User from '../components/User.vue'
  import AsideList from '../components/AsideList.vue'
  import Navigation from '../components/Navigation.vue'
  import ElementListItems from '../components/ElementListItems.vue'
  import { useAuthStore, useDrawerStore, useLanguageStore } from '../stores'
  import ElementDetail from '../views/ElementDetail.vue'

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
      const languages = useLanguageStore()
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer, languages }
    },

    methods: {
      languages() {
        const list = []

        for(const key in this.languages.available) {
          list.push({ title: this.languages.available[key], value: {lang: key} })
        }

        return list
      },


      open(item) {
        this.openView(ElementDetail, {item: item})
      }
    }
  }
</script>

<template>
  <v-app-bar :elevation="0" density="compact">
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
    <v-container>
      <v-sheet class="box">
        <ElementListItems :filter="filter" @select="open($event)" />
      </v-sheet>
    </v-container>
  </v-main>

  <AsideList v-model:filter="filter" :content="[{
      name: 'trashed',
      items: [
        { title: 'Available only', value: {'trashed': 'WITHOUT'} },
        { title: 'Include trashed', value: {'trashed': 'WITH'} },
        { title: 'Only trashed', value: {'trashed': 'ONLY'} }
      ]
    }, {
      name: 'editor',
      items: [
        { title: 'Edited by me', value: {'editor': this.auth.me.email} },
      ]
    }, {
      name: 'language',
      items: languages()
    }]"
  />
</template>

<style scoped>
  .v-main {
    overflow-y: auto;
  }
</style>
