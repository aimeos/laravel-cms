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
      contents: [],
      elements: [],
      versions: [],
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

            this.changed = {}
            this.item.published = true
            this.messages.add('Page published successfully', 'success')
          }).catch(error => {
            this.messages.add('Error publishing page', 'error')
            console.error(`publishPage(id: ${this.item.id})`, error)
          })
        })
      },


      save(quite = false) {
        if(!this.hasChanged) {
          return Promise.resolve(true)
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

        this.validate().then(valid => {
          if(!valid) {
            this.messages.add('There are invalid fields, please resolve the errors first', 'error')
            return valid
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
                meta: JSON.stringify(meta),
                config: JSON.stringify(config),
                contents: JSON.stringify(this.clean(this.contents))
              },
              elements: this.elements.map(entry => entry.id),
              files: files.map(entry => entry.id),
            }
          }).then(response => {
            if(response.errors) {
              throw response.errors
            }

            this.item.published = false
            this.changed = {}

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


      setModified(what) {
        this.changed[what] = true
      },


      use(version, changed = true) {
        this.vhistory = false
        this.changed = {all: changed}
        this.contents = version.contents
        this.$emit('update:item', {...version.data, id: this.item.id})
      },


      validate() {
        return Promise.all([
          this.$refs.page?.validate(),
          this.$refs.content?.validate()
        ].filter(v => v)).then(results => {
          return results.every(result => result)
        })
      }
    },

    watch: {
      item() {
        this.$apollo.query({
          query: gql`query($id: ID!) {
            page(id: $id) {
              contents
              elements {
                id
                type
                data
                editor
                updated_at
              }
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
            id: this.item.id
          }
        }).then(result => {
          if(result.errors || !result.data.page) {
            throw result
          }

          this.changed = {}
          this.versions = (result.data.page.versions || []).toReversed() // latest first
          this.contents = JSON.parse(this.versions.at(0)?.contents || result.data.page.contents || '[]')
          this.elements = (this.versions.at(0)?.elements || result.data.page.elements || []).map(entry => {
            return {...entry, data: JSON.parse(entry.data || '{}')}
          })
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
        :class="{hidden: !versions.length}"
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
    <v-tabs fixed-tabs v-model="tab">
      <v-tab value="page" :class="{changed: changed.page, error: errors.page}">Page</v-tab>
      <v-tab value="content" :class="{changed: changed.content, error: errors.content}">Content</v-tab>
      <v-tab value="preview">Preview</v-tab>
    </v-tabs>

    <v-window v-model="tab">

      <v-window-item value="page">
        <PageDetailsPage ref="page"
          :item="item"
          :versions="versions"
          @update:item="Object.assign(item, $event); setModified('page')"
          @error="errors.page = $event"
        />
      </v-window-item>

      <v-window-item value="content">
        <PageDetailsContent ref="content"
          :item="item"
          :elements="elements"
          :contents="contents"
          @update:contents="contents = $event; setModified('content')"
          @update:elements="elements = $event; setModified('content')"
          @update:files="files = $event; setModified('content')"
          @error="errors.content = $event"
        />
      </v-window-item>

      <v-window-item value="preview">
        <PageDetailsPreview :item="item" />
      </v-window-item>

    </v-window>
  </v-main>

  <Aside v-model:state="nav" />

  <Teleport to="body">
    <v-dialog v-model="vhistory" scrollable width="auto">
      <History
        :data="{
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
          config: clean(item.config),
        }"
        :contents="clean(contents)"
        :versions="versions"
        @use="use($event)"
        @revert="use($event, false)"
        @hide="vhistory = false"
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
