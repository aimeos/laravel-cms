<script>
  import { useAuthStore } from '../stores'

  export default {
    props: {
      'state': {type: [Boolean, null], required: true}
    },

    emits: ['update:state'],

    setup() {
      const auth = useAuthStore()
      return { auth }
    }
  }
</script>

<template>
  <v-navigation-drawer :modelValue="state" @update:modelValue="$emit('update:state', $event)" width="224" location="start" mobile-breakpoint="lg">
    <v-list>
      <v-list-item v-if="auth.can('page:view')" prepend-icon="mdi-file-tree">
        <router-link to="/pages" class="router-link">Pages</router-link>
      </v-list-item>
      <v-list-item v-if="auth.can('element:view')" prepend-icon="mdi-share-variant">
        <router-link to="/elements" class="router-link">Shared elements</router-link>
      </v-list-item>
      <v-list-item v-if="auth.can('file:view')" prepend-icon="mdi-folder-multiple-image">
        <router-link to="/files" class="router-link">Files</router-link>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<style scoped>
  .v-list-item:has(.router-link-active),
  .v-list-item:has(.router-link-active) a {
    background-color: rgb(var(--v-theme-primary));
    color: rgb(var(--v-theme-on-primary));
  }
</style>
