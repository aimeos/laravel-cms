<script>
  import gql from 'graphql-tag'
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
      nav: false,
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

<style>
  .page-tree, .page-details {
    position: absolute;
    min-height: 100vh;
    right: 0;
    left: 0;
    top: 0;
  }
</style>
