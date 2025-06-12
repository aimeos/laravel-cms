<script>
  import PageDetailContentList from './PageDetailContentList.vue'
  import { useConfigStore } from '../stores'

  export default {
    components: {
      PageDetailContentList
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
  <v-container>
    <v-sheet class="box">
      <div v-if="Object.keys(sections).length > 1">
        <v-tabs class="subtabs" v-model="tab" align-tabs="center">
          <v-tab v-for="(list, section) in sections" :key="section"
            :class="{
              changed: changed[section],
              error: errors[section]
            }"
            :value="section"
          >{{ section }}</v-tab>
        </v-tabs>

        <v-window v-model="tab">
          <v-window-item v-for="(list, section) in sections" :key="section" :value="section">
            <PageDetailContentList
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

      <PageDetailContentList v-else
        :item="item"
        :contents="contents"
        :elements="elements"
        :assets="assets"
        @update:contents="$emit('update:contents', $event)"
        @update:elements="$emit('update:elements', $event)"
        @error="$emit('error', $event)"
      />
    </v-sheet>
  </v-container>
</template>

<style scoped>
  .v-sheet {
    margin: 0;
    padding-top: 0;
    padding-bottom: 0;
  }
</style>
