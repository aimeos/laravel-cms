<script>
  import PageDetailsPageConfig from './PageDetailsPageConfig.vue'
  import PageDetailsPageProps from './PageDetailsPageProps.vue'
  import PageDetailsPageMeta from './PageDetailsPageMeta.vue'

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
  <v-form @submit.prevent>
    <v-container>
      <v-tabs class="subtabs" v-model="tab" align-tabs="center" density="compact">
        <v-tab value="details">Details</v-tab>
        <v-tab value="meta">Meta</v-tab>
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
    </v-container>
  </v-form>
</template>

<style scoped>
  .subtabs {
    background-color: rgb(var(--v-theme-surface));
  }
</style>
