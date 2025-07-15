<script>
  import gql from 'graphql-tag'
  import User from '../components/User.vue'
  import FileDetail from '../views//FileDetail.vue'
  import AsideList from '../components/AsideList.vue'
  import Navigation from '../components/Navigation.vue'
  import FileListItems from '../components/FileListItems.vue'
  import { useAuthStore, useDrawerStore, useLanguageStore } from '../stores'

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
        const list = [{ title: this.$gettext('All'), icon: 'mdi-playlist-check', value: {lang: null} }]

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
      title: $gettext('Publish'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'publish': null} },
        { title: $gettext('Published'), icon: 'mdi-publish', value: {'publish': 'PUBLISHED'} },
        { title: $gettext('Scheduled'), icon: 'mdi-clock-outline', value: {'publish': 'SCHEDULED'} },
        { title: $gettext('Drafts'), icon: 'mdi-pencil', value: {'publish': 'DRAFT'} }
      ]
    }, {
      key: 'trashed',
      title: $gettext('Trashed'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'trashed': 'WITH'} },
        { title: $gettext('Available only'), icon: 'mdi-delete-off', value: {'trashed': 'WITHOUT'} },
        { title: $gettext('Only trashed'), icon: 'mdi-delete', value: {'trashed': 'ONLY'} }
      ]
    }, {
      key: 'editor',
      title: $gettext('Editor'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'editor': null} },
        { title: $gettext('Edited by me'), icon: 'mdi-account', value: {'editor': this.auth.me?.email} },
      ]
    }, {
      key: 'lang',
      title: $gettext('Languages'),
      items: languages()
    }]"
  />
</template>

<style scoped>
  .v-main {
    overflow-y: auto;
  }
</style>
