<script>
  import gql from 'graphql-tag'
  import Aside from './Aside.vue'
  import History from './History.vue'
  import PageDetailsPage from './PageDetailsPage.vue'
  import PageDetailsContent from './PageDetailsContent.vue'
  import PageDetailsPreview from './PageDetailsPreview.vue'
  import { useMessageStore } from '../stores'


  export default {
    components: {
      Aside,
      History,
      PageDetailsPage,
      PageDetailsContent,
      PageDetailsPreview
    },

    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item', 'close'],

    data: () => ({
      changed: {},
      errors: {},
      files: {},
      elements: {},
      contents: [],
      latest: null,
      publishAt: null,
      pubmenu: null,
      nav: null,
      tab: 'page',
      vhistory: false,
    }),

    setup() {
      const messages = useMessageStore()
      return { messages }
    },

    computed: {
      hasChanged() {
        return Object.values(this.changed).some(entry => entry)
      },

      hasError() {
        return Object.values(this.errors).some(entry => entry)
      }
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
        this.save(true).then(valid => {
          if(!valid) {
            return
          }

          this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!, $at: DateTime) {
              pubPage(id: $id, at: $at) {
                id
              }
            }`,
            variables: {
              id: this.item.id,
              at: at?.toISOString()?.substring(0, 19)?.replace('T', ' ')
            }
          }).then(response => {
            if(response.errors) {
              throw response.errors
            }

            this.item.published = true
            this.messages.add('Page published successfully', 'success')
          }).catch(error => {
            this.messages.add('Error publishing page', 'error')
            console.error(`publishPage(id: ${this.item.id})`, error)
          })
        })
      },


      reset() {
        this.changed = {}
        this.errors = {}

        this.$refs.page?.reset()
        this.$refs.contents?.reset()
      },


      save(quite = false) {
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
                cache: this.item.cache,
                domain: this.item.domain,
                lang: this.item.lang,
                name: this.item.name,
                slug: this.item.slug,
                status: this.item.status,
                title: this.item.title,
                tag: this.item.tag,
                to: this.item.to,
                type: this.item.type,
                theme: this.item.theme,
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

            if(!quite) {
              this.messages.add('Page saved successfully', 'success')
            }

            return true
          }).catch(error => {
            this.messages.add('Error saving page data', 'error')
            console.error(`savePage(id: ${this.item.id})`, error)
          }).finally(() => {
            this.$emit('close')
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
        if(!id) {
          return Promise.resolve([])
        }

        return this.$apollo.query({
          query: gql`query($id: ID!) {
            page(id: $id) {
              id
              versions {
                published
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

          return result.data.page.versions || []
        }).catch(error => {
          this.messages.add('Error fetching page versions', 'error')
          console.error(`pageversion(id: ${id})`, error)
        })
      }
    },

    watch: {
      item() {
        this.$apollo.query({
          query: gql`query($id: ID!) {
            page(id: $id) {
              id
              contents
              files {
                id
                mime
                name
                path
                previews
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
                  mime
                  name
                  path
                  previews
                  updated_at
                  editor
                }
              }
              latest {
                published
                data
                contents
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
                elements {
                  id
                  type
                  data
                  label
                  editor
                  updated_at
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
            }
          }`,
          variables: {
            id: this.item.id
          }
        }).then(result => {
          if(result.errors || !result.data.page) {
            throw result
          }

          this.reset()
          this.files = {}
          this.elements = {}
          this.latest = result.data.page.latest
          this.contents = JSON.parse(this.latest?.contents || result.data.page.contents || '[]')

          for(const entry of (this.latest?.elements || result.data.page.elements || [])) {
            this.elements[entry.id] = {
              ...entry,
              data: JSON.parse(entry.data || '{}'),
              files: (entry.files || []).map(file => {
                return {...file, previews: JSON.parse(file.previews || '{}')}
              })
            }
          }

          for(const entry of (this.latest?.files || result.data.page.files || [])) {
            this.files[entry.id] = {...entry, previews: JSON.parse(entry.previews || '{}')}
          }
        }).catch(error => {
          this.messages.add('Error fetching page data', 'error')
          console.error(`page(id: ${this.item.id})`, error)
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
        Page: {{ item.name }}
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn icon="mdi-history"
        :class="{hidden: !hasChanged && !latest}"
        @click="vhistory = true"
        elevation="0"
      ></v-btn>

      <v-btn :class="{error: hasError}" :disabled="!hasChanged || hasError" @click="save()" variant="text">
        Save
      </v-btn>

      <v-menu v-model="pubmenu" :close-on-content-click="false">
        <template #activator="{ props }">
          <v-btn-group class="menu-publish" variant="text">
            <v-btn :class="{error: hasError}" class="button" :disabled="!hasChanged || hasError" @click="publish()">Publish</v-btn>
            <v-btn :class="{error: hasError}" class="icon" :disabled="!hasChanged || hasError" v-bind="props" icon="mdi-menu-down"></v-btn>
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

      <v-btn @click.stop="nav = !nav">
        <v-icon size="x-large">
          {{ nav ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <v-main>
    <v-form @submit.prevent>
      <v-tabs fixed-tabs v-model="tab">
        <v-tab value="page" :class="{changed: changed.page, error: errors.page}">Page</v-tab>
        <v-tab value="contents" :class="{changed: changed.contents, error: errors.contents}">Content</v-tab>
        <v-tab value="preview">Preview</v-tab>
      </v-tabs>

      <v-window v-model="tab">

        <v-window-item value="page">
          <PageDetailsPage ref="page"
            :item="item"
            :files="files"
            @update:item="update('page', $event)"
            @error="errors.page = $event"
          />
        </v-window-item>

        <v-window-item value="contents">
          <PageDetailsContent ref="contents"
            :item="item"
            :files="files"
            :elements="elements"
            :contents="contents"
            @update:contents="update('contents', $event)"
            @update:elements="update('elements', $event)"
            @error="errors.contents = $event"
          />
        </v-window-item>

        <v-window-item value="preview">
          <PageDetailsPreview :item="item" />
        </v-window-item>

      </v-window>
    </v-form>
  </v-main>

  <Aside v-model:state="nav" />

  <Teleport to="body">
    <v-dialog v-model="vhistory" scrollable width="auto">
      <History
        :current="{
          data: {
            cache: item.cache,
            domain: item.domain,
            lang: item.lang,
            name: item.name,
            slug: item.slug,
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
        @close="vhistory = false"
      />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
  .v-toolbar__content>.v-toolbar-title {
    margin-inline-start: 0;
  }

  .v-toolbar-title__placeholder {
    text-align: center;
  }

  main .v-tabs {
    margin-bottom: -1rem;
  }

  .v-badge--dot .v-badge__badge {
    margin-inline-start: 0.5rem;
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
