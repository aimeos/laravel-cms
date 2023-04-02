<script>
  import PageTree from '../components/PageTree.vue'
  import PageDetails from '../components/PageDetails.vue'

  export default {
    components: {
      PageTree,
      PageDetails
    },
    data: () => ({
      nav: null,
      id: null,
    }),
  }
</script>

<template>

    <transition-group name="slide">
      <v-layout class="page-tree" key="tree">
        <PageTree
          v-model:nav="nav"
          v-model:id="id"
        />
      </v-layout>
      <v-layout class="page-details" key="details" v-show="id">
        <PageDetails
          v-model:id="id"
        />
      </v-layout>
    </transition-group>

</template>

<style>
  .page-tree, .page-details {
    position: absolute;
    bottom: 0;
    right: 0;
    left: 0;
    top: 0;
  }
</style>
