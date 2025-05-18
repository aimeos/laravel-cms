<script>
  export default {
    props: {
      'state': {type: [Boolean, null], required: true}
    },

    emits: ['update:state'],

    data: () => ({
      width: window.innerWidth
    })
  }
</script>

<template>
  <v-navigation-drawer location="start" rail-width="220" :modelValue="state" @update:modelValue="$emit('update:state', $event)" :rail="width > 1200 ? false : true" expand-on-hover>
    <v-list>
      <v-list-item prepend-icon="mdi-file-tree">
        <router-link to="/pages" class="router-link">Pages</router-link>
      </v-list-item>
      <v-list-item prepend-icon="mdi-folder-multiple-image">
        <router-link to="/files" class="router-link">Files</router-link>
      </v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<style scoped>
.v-navigation-drawer {
  z-index: 0 !important;
}

.v-list-item:has(.router-link-active),
.v-list-item:has(.router-link-active) a {
  background-color: rgb(var(--v-theme-primary));
  color: rgb(var(--v-theme-on-primary));
}
</style>
