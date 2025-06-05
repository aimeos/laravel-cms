<script>
  import gql from 'graphql-tag'
  import User from './User.vue'
  import AsideList from './AsideList.vue'
  import Navigation from './Navigation.vue'
  import PageListItems from './PageListItems.vue'
  import { useAuthStore } from '../stores'

  export default {
    components: {
      PageListItems,
      Navigation,
      AsideList,
      User
    },

    props: {
      'nav': {type: [Boolean, null], default: null}
    },

    emits: ['update:nav', 'update:item'],

    data: () => ({
      aside: null,
      filter: {'trashed': 'WITHOUT'},
    }),

    setup() {
      const auth = useAuthStore()
      return { auth }
    }
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

      <v-btn @click.stop="aside = !aside">
        <v-icon size="x-large">
          {{ aside ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <Navigation :state="nav" @update:state="$emit('update:nav', $event)" />

  <v-main class="page-list">
    <PageListItems @update:item="$emit('update:item', $event)" :filter="filter" />
  </v-main>

  <AsideList v-model:state="aside" v-model:filter="filter" :content="[{
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
