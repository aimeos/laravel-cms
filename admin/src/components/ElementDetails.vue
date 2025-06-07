<script>
  import gql from 'graphql-tag'
  import History from './History.vue'
  import AsideMeta from './AsideMeta.vue'
  import ElementDetailsRefs from './ElementDetailsRefs.vue'
  import ElementDetailsElement from './ElementDetailsElement.vue'
  import { useAuthStore, useDrawerStore, useMessageStore} from '../stores'


  export default {
    components: {
      History,
      AsideMeta,
      ElementDetailsElement,
      ElementDetailsRefs
    },

    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item', 'close'],

    data: () => ({
      assets: {},
      changed: false,
      error: false,
      publishAt: null,
      pubmenu: false,
      vhistory: false,
      tab: 'element',
    }),

    setup() {
      const messages = useMessageStore()
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer, messages }
    },

    methods: {
      publish(at = null) {
        if(!this.auth.can('element:publish')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        this.save(true).then(valid => {
          if(!valid) {
            return
          }

          this.$apollo.mutate({
            mutation: gql`mutation ($id: [ID!]!, $at: DateTime) {
              pubElement(id: $id, at: $at) {
                id
              }
            }`,
            variables: {
              id: [this.item.id],
              at: at?.toISOString()?.substring(0, 19)?.replace('T', ' ')
            }
          }).then(response => {
            if(response.errors) {
              throw response.errors
            }

            if(!at) {
              this.item.published = true
              this.messages.add('Element published successfully', 'success')
            } else {
              this.item.publish_at = at
              this.messages.add(`Element scheduled for publishing at ${at.toLocaleDateString()}`, 'info')
            }

            this.$emit('close')
          }).catch(error => {
            this.messages.add('Error publishing element', 'error')
            this.$log(`ElementDetails::publish(): Error publishing element`, at, error)
          })
        })
      },


      reset() {
        this.changed = false
        this.error = false
      },


      save(quiet = false) {
        if(!this.auth.can('element:save')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve(false)
        }

        if(!this.changed) {
          return Promise.resolve(true)
        }

        return this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $input: ElementInput!, $files: [ID!]) {
            saveElement(id: $id, input: $input, files: $files) {
              id
            }
          }`,
          variables: {
            id: this.item.id,
            input: {
              type: this.item.type,
              name: this.item.name,
              lang: this.item.lang,
              data: JSON.stringify(this.item.data || {}),
            },
            files: this.item.files.filter((id, idx, self) => {
              return self.indexOf(id) === idx && this.assets[id]
            })
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          this.item.published = false
          this.reset()

          if(!quiet) {
            this.messages.add('Element saved successfully', 'success')
          }

          this.$emit('close')
          return true
        }).catch(error => {
          this.messages.add('Error saving element', 'error')
          this.$log(`ElementDetails::save(): Error saving element`, error)
        })
      },


      use(version) {
        Object.assign(this.item, version.data)
        this.vhistory = false
        this.changed = true
      },


      versions(id) {
        if(!this.auth.can('element:view')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve([])
        }

        if(!id) {
          return Promise.resolve([])
        }

        return this.$apollo.query({
          query: gql`query($id: ID!) {
            element(id: $id) {
              id
              versions {
                id
                published
                publish_at
                data
                editor
                created_at
                files {
                  id
                }
              }
            }
          }`,
          variables: {
            id: id
          }
        }).then(result => {
          if(result.errors || !result.data.element) {
            throw result
          }

          return (result.data.element.versions || []).map(v => {
            return {
              ...v,
              data: JSON.parse(v.data || '{}'),
              files: v.files.map(file => file.id),
            }
          }).reverse() // latest versions first
        }).catch(error => {
          this.messages.add('Error fetching element versions', 'error')
          this.$log(`ElementDetails::versions(): Error fetching element versions`, id, error)
        })
      }
    },

    watch: {
      item() {
        if(!this.item?.id || !this.auth.can('element:view')) {
          return
        }

        this.$apollo.query({
          query: gql`query($id: ID!) {
            element(id: $id) {
              id
              files {
                id
                mime
                name
                path
                previews
                updated_at
                editor
              }
              latest {
                id
                published
                data
                editor
                created_at
                files {
                  id
                  mime
                  name
                  path
                  previews
                  updated_at
                  editor
                }
              }
            }
          }`,
          variables: {
            id: this.item.id
          }
        }).then(result => {
          if(result.errors || !result.data.element) {
            throw result
          }

          const files = []
          const element = result.data.element

          this.reset()
          this.assets = {}

          for(const entry of (element.latest?.files || element.files || [])) {
            this.assets[entry.id] = {...entry, previews: JSON.parse(entry.previews || '{}')}
            files.push(entry.id)
          }

          this.item.files = files
        }).catch(error => {
          this.messages.add('Error fetching element', 'error')
          this.$log(`ElementDetails::watch(item): Error fetching element`, error)
        })
      }
    }
  }
</script>

<template>
  <v-app-bar :elevation="1" density="compact">
    <template v-slot:prepend>
      <v-btn icon="mdi-keyboard-backspace"
        @click="$emit('close')"
        elevation="0"
      ></v-btn>
    </template>

    <v-app-bar-title>
      <div class="app-title">
        Element: {{ item.name }}
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn icon="mdi-history"
        :class="{hidden: !changed && !item.latest}"
        @click="vhistory = true"
        elevation="0"
      ></v-btn>

      <v-btn :class="{error: error}"
        :disabled="!changed || error || !auth.can('element:save')"
        @click="save()"
        variant="text"
      >Save</v-btn>

      <v-menu v-model="pubmenu" :close-on-content-click="false">
        <template #activator="{ props }">
          <v-btn-group class="menu-publish" variant="text">
            <v-btn :class="{error: error}" class="button"
              :disabled="item.published && !changed || error || !auth.can('element:publish')"
              @click="publish()"
            >Publish</v-btn>
            <v-btn :class="{error: error}" class="icon" icon="mdi-menu-down"
              :disabled="item.published && !changed || error || !auth.can('element:publish')"
              v-bind="props"
            ></v-btn>
          </v-btn-group>
        </template>
        <div class="menu-content">
          <v-date-picker v-model="publishAt" hide-header show-adjacent-months></v-date-picker>
          <v-btn
            :disabled="!publishAt || error"
            :color="publishAt ? 'primary' : ''"
            @click="publish(publishAt); pubmenu = false"
            variant="flat"
          >Publish</v-btn>
        </div>
      </v-menu>

      <v-btn @click="drawer.toggle('aside')">
        <v-icon size="x-large">
          {{ drawer.aside ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <v-main class="element-details">
    <v-form @submit.prevent>
      <v-tabs fixed-tabs v-model="tab">
        <v-tab value="element" :class="{changed: changed, error: error}">Element</v-tab>
        <v-tab value="refs">Used by</v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <v-window-item value="element">
          <ElementDetailsElement
            :item="item"
            :assets="assets"
            @update:item="this.$emit('update:item', item); changed = true"
            @error="error = $event"
          />
        </v-window-item>

        <v-window-item value="refs">
          <ElementDetailsRefs
            :item="item"
          />
        </v-window-item>

      </v-window>
    </v-form>
  </v-main>

  <AsideMeta :item="item" />

  <Teleport to="body">
    <v-dialog v-model="vhistory" scrollable width="auto">
      <History
        :current="{
          data: {
            lang: item.lang,
            type: item.type,
            name: item.name,
            data: item.data,
          },
          files: item.files,
        }"
        :load="() => versions(item.id)"
        @use="use($event)"
        @revert="use($event); reset()"
        @close="vhistory = false"
      />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
  .v-toolbar-title {
    margin-inline-start: 0;
  }

  .menu-publish {
    margin-inline-start: 4px;
  }

  .menu-publish .button {
    padding: 0;
    padding-inline-start: 16px;
  }

  .menu-content {
    background-color: var(--v-theme-background);
  }

  .menu-content .v-btn {
    width: 100%;
  }
</style>
