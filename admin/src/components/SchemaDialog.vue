<script>
  import ElementListItems from './ElementListItems.vue'
  import SchemaItems from './SchemaItems.vue'

  export default {
    components: {
      ElementListItems,
      SchemaItems,
    },

    props: {
      'modelValue': {type: Boolean, required: true},
    },

    emits: ['update:modelValue', 'add']
  }
</script>

<template>
  <v-dialog :modelValue="modelValue" max-width="1200" scrollable>
    <v-card>
      <template v-slot:append>
        <v-icon @click="$emit('update:modelValue', false)">mdi-close</v-icon>
      </template>
      <template v-slot:title>
        Content Elements
      </template>

      <v-divider></v-divider>

      <v-card-text>
        <SchemaItems type="content" @add="$emit('add', $event)" />
        <v-tabs>
          <v-tab>Shared elements</v-tab>
        </v-tabs>
        <ElementListItems @select="$emit('add', $event)" embed />
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>
  .v-tabs {
    background-color: rgb(var(--v-theme-background));
  }
</style>
