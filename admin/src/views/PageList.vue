<script>
  import gql from 'graphql-tag'
  import User from '../components/User.vue'
  import PageDetail from '../views//PageDetail.vue'
  import AsideList from '../components/AsideList.vue'
  import Navigation from '../components/Navigation.vue'
  import PageListItems from '../components/PageListItems.vue'
  import { useAuthStore, useDrawerStore } from '../stores'

  export default {
    components: {
      PageListItems,
      PageDetail,
      Navigation,
      AsideList,
      User
    },

    inject: ['locales', 'openView'],

    data: () => ({
      filter: {
        view: 'tree',
        trashed: 'WITHOUT',
        publish: null,
        status: null,
        editor: null,
        cache: null,
        lang: null,
      },
    }),

    setup() {
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer }
    },

    methods: {
      languages() {
        const list = [{
          title: this.$gettext('All'),
          icon: 'mdi-playlist-check',
          value: {lang: null}
        }]

        for(const entry of this.locales()) {
          list.push({
            title: entry.title,
            icon: 'mdi-translate',
            value: {lang: entry.value} }
          )
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

    <v-app-bar-title>{{ $gettext('Pages') }} </v-app-bar-title>

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
      key: 'view',
      title: $gettext('view'),
      items: [
        { title: $gettext('Tree'), icon: 'mdi-file-tree', value: {'view': 'tree'} },
        { title: $gettext('List'), icon: 'mdi-format-list-bulleted-square', value: {'view': 'list'} },
      ]
    }, {
      key: 'publish',
      title: $gettext('publish'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'publish': null} },
        { title: $gettext('Published'), icon: 'mdi-publish', value: {'publish': 'PUBLISHED'} },
        { title: $gettext('Scheduled'), icon: 'mdi-clock-outline', value: {'publish': 'SCHEDULED'} },
        { title: $gettext('Drafts'), icon: 'mdi-pencil', value: {'publish': 'DRAFT'} }
      ]
    }, {
      key: 'trashed',
      title: $gettext('trashed'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'trashed': 'WITH'} },
        { title: $gettext('Available only'), icon: 'mdi-delete-off', value: {'trashed': 'WITHOUT'} },
        { title: $gettext('Only trashed'), icon: 'mdi-delete', value: {'trashed': 'ONLY'} }
      ]
    }, {
      key: 'status',
      title: $gettext('status'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'status': null} },
        { title: $gettext('Enabled'), icon: 'mdi-eye-outline', value: {'status': 1} },
        { title: $gettext('Hidden'), icon: 'mdi-eye-remove-outline', value: {'status': 2} },
        { title: $gettext('Disabled'), icon: 'mdi-eye-off-outline', value: {'status': 0} }
      ]
    }, {
      key: 'cache',
      title: $gettext('cache'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'cache': null} },
        { title: $gettext('No cache'), icon: 'mdi-clock-alert-outline', value: {'cache': 0} }
      ]
    }, {
      key: 'editor',
      title: $gettext('editor'),
      items: [
        { title: $gettext('All'), icon: 'mdi-playlist-check', value: {'editor': null} },
        { title: $gettext('Edited by me'), icon: 'mdi-account', value: {'editor': this.auth.me?.email} },
      ]
    }, {
      key: 'lang',
      title: $gettext('languages'),
      items: languages()
    }]"
  />
</template>

<style scoped>
  .v-main {
    overflow-y: auto;
  }
</style>
