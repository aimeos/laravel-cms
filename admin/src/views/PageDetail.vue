<script>
  import gql from 'graphql-tag'
  import AsideMeta from '../components/AsideMeta.vue'
  import AsideCount from '../components/AsideCount.vue'
  import HistoryDialog from '../components/HistoryDialog.vue'
  import PageDetailItem from '../components/PageDetailItem.vue'
  import PageDetailContent from '../components/PageDetailContent.vue'
  import PageDetailPreview from '../components/PageDetailPreview.vue'
  import { useAuthStore, useDrawerStore, useMessageStore } from '../stores'


  export default {
    components: {
      AsideMeta,
      AsideCount,
      HistoryDialog,
      PageDetailItem,
      PageDetailContent,
      PageDetailPreview
    },

    inject: ['closeView'],

    props: {
      'item': {type: Object, required: true}
    },

    data: () => ({
      tab: 'page',
      aside: 'meta',
      asidePage: 'meta',
      changed: {},
      errors: {},
      assets: {},
      elements: {},
      contents: [],
      latest: null,
      pubmenu: null,
      publishAt: null,
      vhistory: false,
    }),

    setup() {
      const messages = useMessageStore()
      const drawer = useDrawerStore()
      const auth = useAuthStore()

      return { auth, drawer, messages }
    },

    computed: {
      hasChanged() {
        return Object.values(this.changed).some(entry => entry)
      },

      hasError() {
        return Object.values(this.errors).some(entry => entry)
      }
    },

    created() {
      if(!this.item?.id || !this.auth.can('page:view')) {
        return
      }

      this.$apollo.query({
        query: gql`query($id: ID!) {
          page(id: $id) {
            id
            contents
            files {
              id
              lang
              mime
              name
              path
              previews
              description
              updated_at
              editor
            }
            elements {
              id
              type
              data
              editor
              updated_at
              files {
                id
                lang
                mime
                name
                path
                previews
                description
                updated_at
                editor
              }
            }
            latest {
              id
              published
              data
              contents
              editor
              created_at
              files {
                id
                lang
                mime
                name
                path
                previews
                description
                updated_at
                editor
              }
              elements {
                id
                type
                data
                name
                editor
                updated_at
                files {
                  id
                  lang
                  mime
                  name
                  path
                  previews
                  description
                  updated_at
                  editor
                }
              }
            }
          }
        }`,
        variables: {
          id: this.item.id
        }
      }).then(result => {
        if(result.errors || !result.data.page) {
          throw result
        }

        const page = result.data.page

        this.reset()
        this.assets = {}
        this.elements = {}
        this.latest = page.latest
        this.contents = JSON.parse(this.latest?.contents || page.contents || '[]')

        for(const entry of (this.latest?.elements || page.elements || [])) {
          this.elements[entry.id] = {
            ...entry,
            data: JSON.parse(entry.data || '{}'),
            files: (entry.files || []).map(file => {
              return {
                ...file,
                previews: JSON.parse(file.previews || '{}'),
                description: JSON.parse(file.description || '{}')
              }
            })
          }
        }

        for(const entry of (this.latest?.files || page.files || [])) {
          this.assets[entry.id] = {
            ...entry,
            previews: JSON.parse(entry.previews || '{}'),
            description: JSON.parse(entry.description || '{}')
          }
        }

        for(const entry of this.contents) {
          if(entry.files && Array.isArray(entry.files)) {
            entry.files = entry.files.filter(id => {
              return typeof this.assets[id] !== 'undefined'
            })
          }
        }
      }).catch(error => {
        this.messages.add('Error fetching page', 'error')
        this.$log(`PageDetail::watch(item): Error fetching page`, error)
      })
    },

    methods: {
      clean(data) {
        for(const key in data) {
          for(const k in data[key]) {
            if(k.startsWith('_')) {
              delete data[key][k]
            }
          }
        }

        return data
      },


      publish(at = null) {
        if(!this.auth.can('page:publish')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        this.save(true).then(valid => {
          if(!valid) {
            return
          }

          this.$apollo.mutate({
            mutation: gql`mutation ($id: [ID!]!, $at: DateTime) {
              pubPage(id: $id, at: $at) {
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
              this.messages.add('Page published successfully', 'success')
            } else {
              this.item.publish_at = at
              this.messages.add(`Page scheduled for publishing at ${at.toLocaleDateString()}`, 'info')
            }

            this.closeView()
          }).catch(error => {
            this.messages.add('Error publishing page', 'error')
            this.$log(`PageDetail::publish(): Error publishing page`, at, error)
          })
        })
      },


      reset() {
        this.$refs.page?.reset()
        this.$refs.contents?.reset()

        this.changed = {}
        this.errors = {}
      },


      save(quiet = false) {
        if(!this.auth.can('page:save')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve(false)
        }

        if(!this.hasChanged) {
          return Promise.resolve(true)
        }

        return this.validate().then(valid => {
          if(!valid) {
            this.messages.add('There are invalid fields, please resolve the errors first', 'error')
            return valid
          }

          const files = []
          for(const entry of (this.contents || [])) {
            files.push(...(entry.files || []))
          }

          const meta = {}
          for(const key in (this.item.meta || {})) {
            meta[key] = {
              type: this.item.meta[key].type || '',
              data: this.item.meta[key].data || {},
              files: this.item.meta[key].files || [],
            }
            files.push(...(this.item.meta[key].files || []))
          }

          const config = {}
          for(const key in (this.item.config || {})) {
            config[key] = {
              type: this.item.config[key].type || '',
              data: this.item.config[key].data || {},
              files: this.item.config[key].files || [],
            }
            files.push(...(this.item.config[key].files || []))
          }

          return this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!, $input: PageInput!, $elements: [ID!], $files: [ID!]) {
              savePage(id: $id, input: $input, elements: $elements, files: $files) {
                id
              }
            }`,
            variables: {
              id: this.item.id,
              input: {
                cache: this.item.cache || 0,
                domain: this.item.domain || '',
                lang: this.item.lang || '',
                name: this.item.name || '',
                path: this.item.path || '',
                status: this.item.status || 0,
                title: this.item.title || '',
                tag: this.item.tag || '',
                to: this.item.to || '',
                type: this.item.type || '',
                theme: this.item.theme || '',
                meta: JSON.stringify(this.clean(meta)),
                config: JSON.stringify(this.clean(config)),
                contents: JSON.stringify(this.clean(this.contents))
              },
              elements: Object.keys(this.elements),
              files: files.filter((id, idx, self) => {
                return self.indexOf(id) === idx
              }),
            }
          }).then(response => {
            if(response.errors) {
              throw response.errors
            }

            this.item.published = false
            this.reset()

            if(!quiet) {
              this.messages.add('Page saved successfully', 'success')
            }

            return true
          }).catch(error => {
            this.messages.add('Error saving page', 'error')
            this.$log(`PageDetail::save(): Error saving page`, error)
          })
        })
      },


      update(what, value) {
        if(what === 'page') {
          Object.assign(this.item, value)
        } else {
          this[what] = value
        }

        this.changed[what] = true
      },


      use(version) {
        Object.assign(this.item, version.data)
        this.contents = version.contents

        this.changed['contents'] = true
        this.changed['page'] = true

        this.vhistory = false
      },


      validate() {
        return Promise.all([
          this.$refs.page?.validate(),
          this.$refs.contents?.validate()
        ].filter(v => v)).then(results => {
          return results.every(result => result)
        })
      },


      versions(id) {
        if(!this.auth.can('page:view')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve([])
        }

        if(!id) {
          return Promise.resolve([])
        }

        return this.$apollo.query({
          query: gql`query($id: ID!) {
            page(id: $id) {
              id
              versions {
                id
                published
                publish_at
                data
                contents
                editor
                created_at
              }
            }
          }`,
          variables: {
            id: id
          }
        }).then(result => {
          if(result.errors || !result.data.page) {
            throw result
          }

          return (result.data.page.versions || []).map(v => {
            return {
              ...v,
              data: JSON.parse(v.data || '{}'),
              contents: v.contents ? JSON.parse(v.contents) : null
            }
          }).reverse() // latest versions first
        }).catch(error => {
          this.messages.add('Error fetching page versions', 'error')
          this.$log(`PageDetail::versions(): Error fetching page versions`, id, error)
        })
      }
    },

    watch: {
      asidePage(newAside) {
        this.aside = newAside
      }
    }
  }
</script>

<template>
  <v-app-bar :elevation="0" density="compact">
    <template v-slot:prepend>
      <v-btn icon="mdi-keyboard-backspace"
        @click="closeView()"
        elevation="0"
      ></v-btn>
    </template>

    <v-app-bar-title>
      <div class="app-title">
        Page: {{ item.name }}
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn icon="mdi-history"
        :class="{hidden: item.published && !hasChanged && !latest}"
        @click="vhistory = true"
        elevation="0"
      ></v-btn>

      <v-btn :class="{error: hasError}" class="menu-save"
        :disabled="!hasChanged || hasError || !auth.can('page:save')"
        @click="save()"
        variant="text"
      >Save</v-btn>

      <v-menu v-model="pubmenu" :close-on-content-click="false">
        <template #activator="{ props }">
          <v-btn-group class="menu-publish" variant="text">
            <v-btn :class="{error: hasError}" class="button"
              :disabled="item.published && !hasChanged || hasError || !auth.can('page:publish')"
              @click="publish()"
            >Publish</v-btn>
            <v-btn :class="{error: hasError}" class="icon" icon="mdi-menu-down"
              :disabled="item.published && !hasChanged || hasError || !auth.can('page:publish')"
              v-bind="props"
            ></v-btn>
          </v-btn-group>
        </template>
        <div class="menu-content">
          <v-date-picker v-model="publishAt" hide-header show-adjacent-months></v-date-picker>
          <v-btn
            :disabled="!publishAt || hasError"
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

  <v-main class="page-details">
    <v-form @submit.prevent>
      <v-tabs fixed-tabs v-model="tab">
        <v-tab value="page"
          :class="{changed: changed.page, error: errors.page}"
          @click="aside = asidePage"
        >Page</v-tab>
        <v-tab value="contents"
          :class="{changed: changed.contents, error: errors.contents}"
          @click="aside = 'count'"
        >Content</v-tab>
        <v-tab value="preview"
          @click="aside = ''"
        >Preview</v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <v-window-item value="page">
          <PageDetailItem ref="page"
            :item="item"
            :assets="assets"
            @update:item="update('page', $event)"
            @update:aside="asidePage = $event"
            @error="errors.page = $event"
          />
        </v-window-item>

        <v-window-item value="contents">
          <PageDetailContent ref="contents"
            :item="item"
            :assets="assets"
            :elements="elements"
            :contents="contents"
            @update:contents="update('contents', $event)"
            @update:elements="update('elements', $event)"
            @error="errors.contents = $event"
          />
        </v-window-item>

        <v-window-item value="preview">
          <PageDetailPreview :item="item" />
        </v-window-item>

      </v-window>
    </v-form>
  </v-main>

  <AsideMeta v-if="aside === 'meta'" :item="item" />
  <AsideCount v-if="aside === 'count'" />

  <Teleport to="body">
    <HistoryDialog
      v-model="vhistory"
      :current="{
        data: {
          cache: item.cache,
          domain: item.domain,
          lang: item.lang,
          name: item.name,
          path: item.path,
          status: item.status,
          title: item.title,
          tag: item.tag,
          to: item.to,
          type: item.type,
          theme: item.theme,
          meta: clean(item.meta),
          config: clean(item.config)
        },
        contents: clean(contents),
      }"
      :load="() => versions(item.id)"
      @use="use($event)"
      @revert="use($event); reset()"
    />
  </Teleport>
</template>

<style scoped>
  .v-toolbar-title {
    margin-inline-start: 0;
  }
</style>
