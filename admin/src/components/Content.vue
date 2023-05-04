<script>
  import Element from './Element.vue'

  export default {
    components: {
      Element
    },
    props: ['clip', 'checked', 'content'],
    emits: ['copy', 'cut', 'insert', 'paste', 'remove', 'update:checked', 'update:content'],
    data: () => ({
      data: {}
    }),
    methods: {
      use(data) {
        this.data = data
      }
    },
    watch: {
      content: {
        immediate: true,
        handler(content, old) {
          if(content != old) {
            this.data = {...content}
          }
        }
      },

      data: {
        deep: true,
        handler(data, old) {
          if(data != old) {
            this.$emit('update:content', data)
          }
        }
      }
    }
  }
</script>

<template>
  <v-expansion-panel elevation="1">
    <v-expansion-panel-title collapse-icon="mdi-pencil">
      <v-checkbox-btn :model-value="checked" @click.stop="$emit('update:checked', !checked)">
      </v-checkbox-btn>
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


      <div class="panel-heading">
        {{ data.type }}
        <span class="subtext">{{ data.title || data.text || '' }}</span>
      </div>
    </v-expansion-panel-title>
    <v-expansion-panel-text>

      <Element v-model:data="data" />

    </v-expansion-panel-text>
  </v-expansion-panel>
</template>

<style scoped>
</style>
