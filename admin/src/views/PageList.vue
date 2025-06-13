<script>
  import gql from 'graphql-tag'
  import User from '../components/User.vue'
  import AsideList from '../components/AsideList.vue'
  import Navigation from '../components/Navigation.vue'
  import PageListItems from '../components/PageListItems.vue'
  import { useAuthStore, useDrawerStore, useLanguageStore } from '../stores'
  import PageDetail from '../views//PageDetail.vue'

  export default {
    components: {
      PageListItems,
      PageDetail,
      Navigation,
      AsideList,
      User
    },

    inject: ['openView'],

    data: () => ({
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
          list.push({ title: this.languages.available[key], icon: 'mdi-translate', value: {lang: key} })
        }

        return list
      },


      open(item) {
        this.openView(PageDetail, {item: item})
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
    <v-container>
      <v-sheet class="box">
        <PageListItems @select="open($event)" :filter="filter" />
      </v-sheet>
    </v-container>
  </v-main>

  <AsideList v-model:filter="filter" :content="[{
      name: 'publish',
      items: [
        { title: 'All', icon: 'mdi-playlist-check', value: {'publish': null} },
        { title: 'Published', icon: 'mdi-publish', value: {'publish': 'PUBLISHED'} },
        { title: 'Scheduled', icon: 'mdi-clock-outline', value: {'publish': 'SCHEDULED'} },
        { title: 'Drafts', icon: 'mdi-pencil', value: {'publish': 'DRAFT'} }
      ]
    }, {
      name: 'trashed',
      items: [
        { title: 'Available only', icon: 'mdi-delete-off', value: {'trashed': 'WITHOUT'} },
        { title: 'Include trashed', icon: 'mdi-delete-outline', value: {'trashed': 'WITH'} },
        { title: 'Only trashed', icon: 'mdi-delete', value: {'trashed': 'ONLY'} }
      ]
    }, {
      name: 'editor',
      items: [
        { title: 'Edited by me', icon: 'mdi-account', value: {'editor': this.auth.me.email} },
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
