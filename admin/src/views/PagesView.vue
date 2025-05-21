<script>
  import PageTree from '../components/PageTree.vue'
  import PageDetails from '../components/PageDetails.vue'
  import { useMessageStore } from '../stores'

  export default {
    components: {
      PageTree,
      PageDetails
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
      <v-layout class="page-tree" key="tree">
        <PageTree
          v-model:nav="nav"
          v-model:item="item"
          @update:item="details = true"
        />
      </v-layout>
      <v-layout class="page-details" key="details" v-show="details">
        <PageDetails
          v-model:item="item"
          @close="details = false"
        />
      </v-layout>
    </transition-group>

    <v-snackbar-queue v-model="messages.queue"></v-snackbar-queue>
</template>

<style scoped>
  .page-tree, .page-details {
    background: rgb(var(--v-theme-background));
    overflow-y: auto;
    position: fixed;
    height: 100vh;
    width: 100vw;
    left: 0;
    top: 0;
  }
</style>
