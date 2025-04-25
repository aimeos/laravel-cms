<script>
  import gql from 'graphql-tag'
  import PageTree from '../components/PageTree.vue'
  import PageDetails from '../components/PageDetails.vue'

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
