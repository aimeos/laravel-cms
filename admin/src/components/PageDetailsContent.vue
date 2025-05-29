<script>
  import PageDetailsContentList from './PageDetailsContentList.vue'
  import { useConfigStore } from '../stores'

  export default {
    components: {
      PageDetailsContentList
    },

    props: {
      'item': {type: Object, required: true},
      'contents': {type: Array, required: true},
      'elements': {type: Object, required: true},
      'assets': {type: Object, default: () => ({})}
    },

    emits: ['error', 'update:contents',  'update:elements'],

    data: () => ({
      changed: {},
      errors: {},
      tab: 'default',
    }),

    setup() {
      const config = useConfigStore()
      return { config }
    },

    computed: {
      names() {
        const type = this.item.type || 'default'
        const theme = this.item.theme || 'default'

        return this.config.get(`themes.${theme}.types.${type}.sections`, ['main'])
      },


      sections() {
        if(this.names.length <= 1) {
          return {}
        }

        const sections = {}

        for(const name of this.names) {
          sections[name] = []
        }

        for(const item of this.contents) {
          const name = item.group || 'main'

          if(!sections[name]) {
            sections[name] = []
          }

          sections[name].push(item)
        }

        return sections
      }
    },

    methods: {
      error(what, value) {
        this.errors[what] = value
        this.$emit('error', Object.values(this.errors).includes(true))
      },


      reset() {
        this.changed = {}
        this.errors = {}

        Object.values(this.$refs).map(ref => ref.reset())
      },


      update(section, list) {
        const sections = this.sections
        sections[section] = list

        const contents = Object.values(sections).reduce((acc, entries) => {
          return acc.concat(entries)
        }, [])

        this.$emit('update:contents', contents)
        this.changed[section] = true
      },


      validate() {
        const promises = Object.values(this.$refs).map(ref => ref.validate())

        return Promise.all(promises).then(results => {
          return results.every(result => result)
        })
      }
    }
  }
</script>

<template>
  <div v-if="Object.keys(sections).length > 1">
    <v-container>
      <v-sheet>
        <v-tabs class="subtabs" v-model="tab" align-tabs="center" density="compact">
          <v-tab v-for="(list, section) in sections" :key="section"
            :class="{
              changed: changed[section],
              error: errors[section]
            }"
            :value="section"
          >{{ section }}</v-tab>
        </v-tabs>
      </v-sheet>
    </v-container>

    <v-window v-model="tab">

      <v-window-item v-for="(list, section) in sections" :key="section" :value="section">
        <PageDetailsContentList
          :section="section"
          :item="item"
          :contents="list"
          :elements="elements"
          :assets="assets"
          @update:contents="update(section, $event)"
          @update:elements="$emit('update:elements', $event)"
          @error="error(section, $event)"
        />
      </v-window-item>

    </v-window>
  </div>

  <PageDetailsContentList v-else
    :item="item"
    :contents="contents"
    :elements="elements"
    :assets="assets"
    @update:contents="$emit('update:contents', $event)"
    @update:elements="$emit('update:elements', $event)"
    @error="$emit('error', $event)"
  />
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
