<script>
  import ElementListItems from './ElementListItems.vue'
  import SchemaItems from './SchemaItems.vue'

  export default {
    components: {
      ElementListItems,
      SchemaItems,
    },

    emits: ['add']
  }
</script>

<template>
  <v-container>
    <v-sheet>
      <SchemaItems type="content" @add="$emit('add', $event)" />
      <v-tabs>
        <v-tab>Shared elements</v-tab>
      </v-tabs>
      <ElementListItems @update:item="$emit('add', $event)" embed />
    </v-sheet>
  </v-container>
</template>

<style scoped>
  .v-sheet {
    height: calc(100vh - 64px);
    overflow-y: scroll !important;
    overflow: hidden;
  }

  .v-tabs {
    background-color: rgb(var(--v-theme-background));
  }
</style>
