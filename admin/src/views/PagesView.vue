<script>
  import PageList from '../components/PageList.vue'
  import PageDetails from '../components/PageDetails.vue'
  import { useMessageStore } from '../stores'

  export default {
    components: {
      PageList,
      PageDetails
    },

    data: () => ({
      details: false,
      nav: null,
      item: {},
    }),

    setup() {
      const message = useMessageStore()
      return { message }
    },
  }
</script>

<template>
    <transition-group name="slide">
      <v-layout class="page-list" key="list">
        <PageList
          v-model:nav="nav"
          @update:item="item = $event; details = true"
        />
      </v-layout>
      <v-layout class="page-details" key="details" v-show="details">
        <PageDetails
          v-model:item="item"
          @close="details = false"
        />
      </v-layout>
    </transition-group>

    <v-snackbar-queue v-model="message.queue"></v-snackbar-queue>
</template>

<style scoped>
  .page-list, .page-details {
    background: rgb(var(--v-theme-background));
    overflow-y: auto;
    position: fixed;
    height: 100vh;
    width: 100%;
    left: 0;
    top: 0;
  }
</style>
