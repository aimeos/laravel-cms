<script>
  import ElementList from '../components/ElementList.vue'
  import ElementDetails from '../components/ElementDetails.vue'
  import { useMessageStore } from '../stores'

  export default {
    components: {
      ElementList,
      ElementDetails
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
      <v-layout class="element-list" key="list">
        <ElementList
          v-model:nav="nav"
          @update:item="item = $event; details = true"
        />
      </v-layout>
      <v-layout class="element-details" key="details" v-show="details">
        <ElementDetails
          v-model:item="item"
          @close="details = false"
        />
      </v-layout>
    </transition-group>

    <v-snackbar-queue v-model="message.queue"></v-snackbar-queue>
</template>

<style scoped>
  .element-list, .element-details {
    background: rgb(var(--v-theme-background));
    overflow-y: auto;
    position: fixed;
    height: 100vh;
    width: 100vw;
    left: 0;
    top: 0;
  }
</style>
