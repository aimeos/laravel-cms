<script>
  import PageDetailsPageMeta from './PageDetailsPageMeta.vue'
  import PageDetailsPageProps from './PageDetailsPageProps.vue'
  import PageDetailsPageConfig from './PageDetailsPageConfig.vue'

  export default {
    components: {
      PageDetailsPageMeta,
      PageDetailsPageProps,
      PageDetailsPageConfig,
    },

    props: {
      'item': {type: Object, required: true},
      'versions': {type: Array, default: () => []}
    },

    emits: ['update:item', 'error'],

    data: () => ({
      changed: {},
      errors: {},
      tab: 'details',
    }),

    methods: {
      error(what, value) {
        this.errors[what] = value
        this.$emit('error', Object.values(this.errors).includes(true))
      },


      update(what) {
        this.changed[what] = true
        this.$emit('update:item', this.item)
      }
    }
  }
</script>

<template>
  <v-form @submit.prevent>
    <v-container>
      <v-sheet>
        <v-tabs class="subtabs" v-model="tab" align-tabs="center" density="compact">
          <v-tab value="details" :class="{changed: changed.details, error: errors.details}">Details</v-tab>
          <v-tab value="meta" :class="{changed: changed.meta, error: errors.meta}">Meta</v-tab>
          <v-tab value="config" :class="{changed: changed.config, error: errors.config}">Config</v-tab>
        </v-tabs>

        <v-window v-model="tab">

          <v-window-item value="details">
            <PageDetailsPageProps :item="item" @change="update('details')" @error="error('details', $event)" />
          </v-window-item>

          <v-window-item value="meta">
            <PageDetailsPageMeta :item="item" @change="update('meta')" @error="error('meta', $event)" />
          </v-window-item>

          <v-window-item value="config">
            <PageDetailsPageConfig :item="item" @change="update('config')" @error="error('config', $event)" />
          </v-window-item>

        </v-window>
      </v-sheet>
    </v-container>
  </v-form>
</template>

<style scoped>
  .v-sheet {
    padding-top: 0;
    padding-bottom: 0;
  }

  .v-window-item {
    padding: 0 0.25rem;
  }
</style>
