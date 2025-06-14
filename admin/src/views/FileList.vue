<script>
  import gql from 'graphql-tag'
  import User from '../components/User.vue'
  import AsideList from '../components/AsideList.vue'
  import Navigation from '../components/Navigation.vue'
  import FileListItems from '../components/FileListItems.vue'
  import { useAuthStore, useDrawerStore, useLanguageStore } from '../stores'
  import FileDetail from '../views//FileDetail.vue'

  export default {
    components: {
      FileListItems,
      FileDetail,
      Navigation,
      AsideList,
      User
    },

    inject: ['openView'],

    data: () => ({
      filter: {
        trashed: 'WITHOUT',
        publish: null,
        editor: null,
        lang: null,
      },
    }),

    setup() {
      const languages = useLanguageStore()
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer, languages }
    },

    methods: {
      languages() {
        const list = [{ title: 'All', icon: 'mdi-playlist-check', value: {lang: null} }]

        for(const key in this.languages.available) {
          list.push({ title: this.languages.available[key], icon: 'mdi-translate', value: {lang: key} })
        }

        return list
      },


      open(item) {
        this.openView(FileDetail, {item: item})
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

    <v-app-bar-title>Files</v-app-bar-title>

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

  <v-main class="file-list">
    <v-container>
      <v-sheet class="box">
        <FileListItems @select="open($event)" :filter="filter" />
      </v-sheet>
    </v-container>
  </v-main>

  <AsideList v-model:filter="filter" :content="[{
      key: 'publish',
      items: [
        { title: 'All', icon: 'mdi-playlist-check', value: {'publish': null} },
        { title: 'Published', icon: 'mdi-publish', value: {'publish': 'PUBLISHED'} },
        { title: 'Scheduled', icon: 'mdi-clock-outline', value: {'publish': 'SCHEDULED'} },
        { title: 'Drafts', icon: 'mdi-pencil', value: {'publish': 'DRAFT'} }
      ]
    }, {
      key: 'trashed',
      items: [
        { title: 'All', icon: 'mdi-playlist-check', value: {'trashed': 'WITH'} },
        { title: 'Available only', icon: 'mdi-delete-off', value: {'trashed': 'WITHOUT'} },
        { title: 'Only trashed', icon: 'mdi-delete', value: {'trashed': 'ONLY'} }
      ]
    }, {
      key: 'editor',
      items: [
        { title: 'All', icon: 'mdi-playlist-check', value: {'editor': null} },
        { title: 'Edited by me', icon: 'mdi-account', value: {'editor': this.auth.me.email} },
      ]
    }, {
      key: 'lang',
      items: languages()
    }]"
  />
</template>

<style scoped>
  .v-main {
    overflow-y: auto;
  }
</style>
