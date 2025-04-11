<script>
  import Fields from './Fields.vue'

  export default {
    components: {
      Fields
    },
    props: ['clip', 'content'],
    emits: ['copy', 'cut', 'insert', 'paste', 'remove', 'update:content'],
  }
</script>

<template>
  <v-expansion-panel elevation="1" rounded="lg">
    <v-expansion-panel-title expand-icon="mdi-pencil">
      <v-checkbox-btn v-model="content._checked"></v-checkbox-btn>

      <v-menu>
        <template v-slot:activator="{ props }">
          <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
        </template>
        <v-list>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-copy" variant="text" @click="$emit('copy')">Copy</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-cut" variant="text" @click="$emit('cut')">Cut</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('insert', 0)">Insert before</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('insert', 1)">Insert after</v-btn>
          </v-list-item>
          <v-list-item v-if="clip">
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('paste', 0)">Paste before</v-btn>
          </v-list-item>
          <v-list-item v-if="clip">
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click="$emit('paste', 1)">Paste after</v-btn>
          </v-list-item>
          <v-list-item>
            <v-btn prepend-icon="mdi-delete" variant="text" @click="$emit('remove')">Delete</v-btn>
          </v-list-item>
        </v-list>
      </v-menu>

      <div class="content-type">{{ content.type }}</div>
      <div class="content-title">{{ content.data?.title || content.data?.text || '' }}</div>
    </v-expansion-panel-title>
    <v-expansion-panel-text>

      <Fields :fields="content.fields" v-model:data="content.data" />

    </v-expansion-panel-text>
  </v-expansion-panel>
</template>

<style scoped>
  .v-expansion-panel-title .v-selection-control {
    flex: none;
  }

  .v-expansion-panel-title .content-type {
    min-width: 10rem;
  }

  .v-expansion-panel-title .content-title {
    font-weight: bold;
  }
</style>
