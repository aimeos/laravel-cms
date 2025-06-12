<script>
  import { useAuthStore, useDrawerStore } from '../stores'

  export default {
    setup() {
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer }
    }
  }
</script>

<template>
  <v-navigation-drawer v-model="drawer.nav" location="start" mobile-breakpoint="lg">
    <v-list>
      <v-list-item v-if="auth.can('page:view')" prepend-icon="mdi-file-tree" rounded="lg">
        <router-link to="/pages" class="router-link">Pages</router-link>
      </v-list-item>
      <v-list-item v-if="auth.can('element:view')" prepend-icon="mdi-share-variant" rounded="lg">
        <router-link to="/elements" class="router-link">Shared elements</router-link>
      </v-list-item>
      <v-list-item v-if="auth.can('file:view')" prepend-icon="mdi-folder-multiple-image" rounded="lg">
        <router-link to="/files" class="router-link">Files</router-link>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<style scoped>
  .v-navigation-drawer {
    border-top-right-radius: 8px;
  }

  .v-locale--is-rtl .v-navigation-drawer {
    border-top-right-radius: 0;
    border-top-left-radius: 8px;
  }

  .v-list-item {
    margin: 0 4px;
  }

  .v-list-item:has(.router-link-active),
  .v-list-item:has(.router-link-active) a {
    background-color: rgb(var(--v-theme-surface-light));
    color: rgb(var(--v-theme-on-surface-light));
  }

  a.router-link:focus,
  a.router-link:visited {
    color: rgb(var(--v-theme-on-surface-light));
  }
</style>
