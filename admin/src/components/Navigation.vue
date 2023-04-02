<script>
  export default {
    props: ['state'],
    emits: ['update:state'],
    data: () => ({
      width: window.innerWidth
    })
  }
</script>

<template>
  <v-navigation-drawer width="220" :modelValue="state" @update:modelValue="$emit('update:state', $event)" :rail="width > 1200 ? false : true" expand-on-hover>
    <v-list>
      <v-list-item prepend-icon="mdi-file-tree" title="Pages" value="pages"></v-list-item>
    </v-list>

    <v-divider></v-divider>

    <v-list>
      <v-list-item prepend-icon="mdi-folder-multiple-image" title="Files" value="files"></v-list-item>
    </v-list>
  </v-navigation-drawer>
</template>

<style scoped>
</style>
