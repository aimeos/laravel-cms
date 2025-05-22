<script>
  import FileList from '../components/FileList.vue'
  import FileDetails from '../components/FileDetails.vue'
  import { useMessageStore } from '../stores'

  export default {
    components: {
      FileList,
      FileDetails
    },

    data: () => ({
      details: false,
      nav: null,
      item: {},
    }),

    setup() {
      const messages = useMessageStore()
      return { messages }
    },
  }
</script>

<template>
    <transition-group name="slide">
      <v-layout class="file-list" key="list">
        <FileList
          v-model:nav="nav"
          @update:item="item = $event; details = true"
        />
      </v-layout>
      <v-layout class="file-details" key="details" v-show="details">
        <FileDetails
          v-model:item="item"
          @close="details = false"
        />
      </v-layout>
    </transition-group>

    <v-snackbar-queue v-model="messages.queue"></v-snackbar-queue>
</template>

<style scoped>
  .file-list, .file-details {
    background: rgb(var(--v-theme-background));
    overflow-y: auto;
    position: fixed;
    height: 100vh;
    width: 100vw;
    left: 0;
    top: 0;
  }
</style>
