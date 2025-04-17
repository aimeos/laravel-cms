<script>
  import History from './History.vue'
  import PageDetailsPageMeta from './PageDetailsPageMeta.vue'
  import PageDetailsPageProps from './PageDetailsPageProps.vue'
  import PageDetailsPageConfig from './PageDetailsPageConfig.vue'

  export default {
    components: {
      History,
      PageDetailsPageMeta,
      PageDetailsPageProps,
      PageDetailsPageConfig,
    },
    props: {
      'item': {type: Object, required: true},
      'versions': {type: Array, default: () => []}
    },
    emits: ['update:item'],
    data: () => ({
      tab: 'details',
      vhistory: false,
    }),
    computed: {
      history() {
        return this.vhistory ? true : false
      }
    },
    methods: {
      use(data) {
        this.item.data = data
        this.vhistory = false
      }
    },
  }
</script>

<template>
  <v-form @submit.prevent>
    <v-container>
      <v-sheet>
        <v-tabs class="subtabs" v-model="tab" align-tabs="center" density="compact">
          <v-tab value="details">Details</v-tab>
          <v-tab value="meta">Meta</v-tab>
          <v-tab value="config">Config</v-tab>
        </v-tabs>

        <div class="header">
          <v-btn icon="mdi-history"
            :class="{hidden: !versions.length}"
            @click="vhistory = true"
            variant="outlined"
            elevation="0"
          ></v-btn>
        </div>

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
    </v-container>
  </v-form>

  <Teleport to="body">
    <v-dialog v-model="vhistory" scrollable width="auto">
      <History :data="item.data" :versions="versions" @use="use($event)" @hide="vhistory = false" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
  .v-sheet {
    padding-top: 0;
    padding-bottom: 0;
  }

  .v-window-item {
    padding: 0 0.25rem;
  }

  .header {
    display: flex;
    justify-content: end;
  }
</style>
