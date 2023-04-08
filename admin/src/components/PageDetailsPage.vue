<script>
  import PageDetailsPageConfig from '../components/PageDetailsPageConfig.vue'
  import PageDetailsPageProps from '../components/PageDetailsPageProps.vue'
  import PageDetailsPageMeta from '../components/PageDetailsPageMeta.vue'

  export default {
    components: {
      PageDetailsPageConfig,
      PageDetailsPageProps,
      PageDetailsPageMeta
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      tab: 'details',
    })
  }
</script>

<template>
    <v-sheet>
      <v-tabs class="subtabs" v-model="tab" align-tabs="center" density="compact">
        <v-tab value="details">Details</v-tab>
        <v-tab value="meta" class="unpublished">Meta</v-tab>
        <v-tab value="config">Config</v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <v-window-item value="details">
          <PageDetailsPageProps :item="item" @update:item="$emit('update:item', $event)" />
        </v-window-item>

        <v-window-item value="meta">
          <PageDetailsPageMeta :item="item" @update:item="$emit('update:item', $event)" />
        </v-window-item>

        <v-window-item value="config">
          <PageDetailsPageConfig :item="item" @update:item="$emit('update:item', $event)" />
        </v-window-item>

      </v-window>
    </v-sheet>
</template>

<style scoped>
  .subtabs {
    margin-bottom: 2rem;
  }

  .btn-group {
    padding: 1rem 0;
  }
</style>
