<script>
  import Aside from './Aside.vue'
  import { useMessageStore } from '../stores'


  export default {
    components: {
      Aside
    },

    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item', 'close'],

    data: () => ({
      changed: {},
      errors: {},
      nav: null,
      tab: 'file',
    }),

    setup() {
      const messages = useMessageStore()
      return { messages }
    },
  }
</script>

<template>
  <v-app-bar :elevation="1" density="compact">
    <template v-slot:prepend>
      <v-btn icon="mdi-keyboard-backspace"
        @click="$emit('close')"
        elevation="0"
      ></v-btn>
    </template>

    <v-app-bar-title>
      <div class="app-title">
        File: {{ item.name }}
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn :class="{error: false}" :disabled="false" @click="save()" variant="text">
        Save
      </v-btn>

      <v-btn @click.stop="nav = !nav">
        <v-icon size="x-large">
          {{ nav ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <v-main class="file-details">
    <v-form @submit.prevent>
      <v-tabs fixed-tabs v-model="tab">
        <v-tab value="file" :class="{changed: true, error: false}">File</v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <v-window-item value="file">
        </v-window-item>

      </v-window>
    </v-form>
  </v-main>

  <Aside v-model:state="nav" />
</template>

<style scoped>
  .v-toolbar__content>.v-toolbar-title {
    margin-inline-start: 0;
  }

  .v-toolbar-title__placeholder {
    text-align: center;
  }
</style>
