<script>
  import gql from 'graphql-tag'
  import History from '../components/History.vue'
  import AsideMeta from '../components/AsideMeta.vue'
  import FileDetailRefs from '../components/FileDetailRefs.vue'
  import FileDetailFile from '../components/FileDetailFile.vue'
  import { useAuthStore, useDrawerStore, useMessageStore } from '../stores'


  export default {
    components: {
      History,
      AsideMeta,
      FileDetailFile,
      FileDetailRefs
    },

    inject: ['closeView'],

    props: {
      'item': {type: Object, required: true}
    },

    data: () => ({
      changed: false,
      error: false,
      publishAt: null,
      pubmenu: false,
      vhistory: false,
      tab: 'file',
    }),

    setup() {
      const messages = useMessageStore()
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer, messages }
    },

    methods: {
      publish(at = null) {
        if(!this.auth.can('file:publish')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        this.save(true).then(valid => {
          if(!valid) {
            return
          }

          this.$apollo.mutate({
            mutation: gql`mutation ($id: [ID!]!, $at: DateTime) {
              pubFile(id: $id, at: $at) {
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
              this.messages.add('File published successfully', 'success')
            } else {
              this.item.publish_at = at
              this.messages.add(`File scheduled for publishing at ${at.toLocaleDateString()}`, 'info')
            }

            this.closeView()
          }).catch(error => {
            this.messages.add('Error publishing file', 'error')
            this.$log(`FileDetail::publish(): Error publishing file`, at, error)
          })
        })
      },


      reset() {
        this.changed = false
        this.error = false
      },


      save(quiet = false) {
        if(!this.auth.can('file:save')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve(false)
        }

        if(!this.changed) {
          return Promise.resolve(true)
        }

        return this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $input: FileInput!) {
            saveFile(id: $id, input: $input) {
              id
            }
          }`,
          variables: {
            id: this.item.id,
            input: {
              description: JSON.stringify(this.item.description || {}),
              name: this.item.name,
              tag: this.item.tag,
            }
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          this.item.published = false
          this.reset()

          if(!quiet) {
            this.messages.add('File saved successfully', 'success')
          }

          this.closeView()
          return true
        }).catch(error => {
          this.messages.add('Error saving file', 'error')
          this.$log(`FileDetail::save(): Error saving file`, error)
        })
      },


      use(version) {
        Object.assign(this.item, version.data)
        this.vhistory = false
        this.changed = true
      },


      versions(id) {
        if(!this.auth.can('file:view')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve([])
        }

        if(!id) {
          return Promise.resolve([])
        }

        return this.$apollo.query({
          query: gql`query($id: ID!) {
            file(id: $id) {
              id
              versions {
                id
                published
                publish_at
                data
                editor
                created_at
              }
            }
          }`,
          variables: {
            id: id
          }
        }).then(result => {
          if(result.errors || !result.data.file) {
            throw result
          }

          return (result.data.file.versions || []).map(v => {
            return {
              ...v,
              data: JSON.parse(v.data || '{}'),
            }
          }).reverse() // latest versions first
        }).catch(error => {
          this.messages.add('Error fetching file versions', 'error')
          this.$log(`FileDetail::versions(): Error fetching file versions`, id, error)
        })
      }
    }
  }
</script>

<template>
  <v-app-bar :elevation="1" density="compact">
    <template v-slot:prepend>
      <v-btn icon="mdi-keyboard-backspace"
        @click="closeView()"
        elevation="0"
      ></v-btn>
    </template>

    <v-app-bar-title>
      <div class="app-title">
        File: {{ item.name }}
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn icon="mdi-history"
        :class="{hidden: !changed && !item.latest}"
        @click="vhistory = true"
        elevation="0"
      ></v-btn>

      <v-btn :class="{error: error}"
        :disabled="!changed || error || !auth.can('file:save')"
        @click="save()"
        variant="text"
      >Save</v-btn>

      <v-menu v-model="pubmenu" :close-on-content-click="false">
        <template #activator="{ props }">
          <v-btn-group class="menu-publish" variant="text">
            <v-btn :class="{error: error}" class="button"
              :disabled="item.published && !changed || error || !auth.can('file:publish')"
              @click="publish()"
            >Publish</v-btn>
            <v-btn :class="{error: error}" class="icon" icon="mdi-menu-down"
              :disabled="item.published && !changed || error || !auth.can('file:publish')"
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

      <v-btn @click.stop="drawer.toggle('aside')">
        <v-icon size="x-large">
          {{ drawer.aside ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <v-main class="file-details">
    <v-form @submit.prevent>
      <v-tabs fixed-tabs v-model="tab">
        <v-tab value="file" :class="{changed: changed, error: error}">File</v-tab>
        <v-tab value="refs">Used by</v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <v-window-item value="file">
          <FileDetailFile
            :item="item"
            @update:item="this.$emit('update:item', item); changed = true"
            @error="error = $event"
          />
        </v-window-item>

        <v-window-item value="refs">
          <FileDetailRefs
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
            tag: item.tag,
            name: item.name,
            mime: item.mime,
            path: item.path,
            previews: item.previews,
            description: item.description,
          },
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

  .v-window {
    margin: 0 1%;
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
