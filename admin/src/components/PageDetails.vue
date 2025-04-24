<script>
  import gql from 'graphql-tag'
  import Aside from './Aside.vue'
  import PageDetailsPage from './PageDetailsPage.vue'
  import PageDetailsContent from './PageDetailsContent.vue'
  import PageDetailsPreview from './PageDetailsPreview.vue'


  export default {
    components: {
      Aside,
      PageDetailsPage,
      PageDetailsContent,
      PageDetailsPreview
    },

    props: {
      'item': {type: Object, required: true}
    },

    emits: ['close'],

    data: () => ({
      changed: false,
      contents: [],
      elements: [],
      versions: [],
      nav: null,
      tab: 'page',
    }),

    methods: {
      clean(entries) {
        return entries.map(c => {
          for(const key in c) {
            if(key.startsWith('_')) {
              delete c[key]
            }
          }
          return c
        })
      },


      save() {
        if(!this.changed) {
          return
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
          }
          files.push(...(this.item.meta[key].files || []))
        }

        const config = {}
        for(const key in (this.item.config || {})) {
          config[key] = {
            type: this.item.config[key].type || '',
            data: this.item.config[key].data || {},
          }
          files.push(...(this.item.config[key].files || []))
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $input: PageInput!) {
            savePage(id: $id, input: $input) {
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
          this.changed = false
        }).catch(error => {
          console.error(`savePage(id: ${this.item.id})`, error)
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

          const latest = result.data.page.versions?.at(-1)

          this.elements = (latest?.elements || result.data.page.elements || []).map(entry => {
            return {...entry, data: JSON.parse(entry.data || '{}')}
          })
          this.contents = JSON.parse(latest?.contents || result.data.page.contents || '[]')
          this.versions = result.data.page.versions || []
          this.changed = false
        }).catch(error => {
          console.error(`page(id: ${this.item.id})`, error)
        })
      }
    }
  }
</script>

<template>
  <v-app-bar :elevation="2" density="compact">
    <v-app-bar-title>
      <div class="app-title" @click="$emit('close'); save()">
        <v-icon icon="mdi-keyboard-backspace"></v-icon>
        Back to pages
      </div>
    </v-app-bar-title>

    <template v-slot:append>
      <v-btn @click.stop="nav = !nav">
        <v-icon size="x-large">
          {{ nav ? 'mdi-chevron-right' : 'mdi-chevron-left' }}
        </v-icon>
      </v-btn>
    </template>
  </v-app-bar>

  <v-main>
    <v-tabs fixed-tabs v-model="tab">
      <v-tab value="page">Page</v-tab>
      <v-tab value="content">Content</v-tab>
      <v-tab value="preview">Preview</v-tab>
    </v-tabs>

    <v-window v-model="tab">

      <v-window-item value="page">
        <PageDetailsPage :item="item" :versions="versions"
          @update:item="Object.assign(item, $event); changed = true"
        />
      </v-window-item>

      <v-window-item value="content">
        <PageDetailsContent :item="item" :elements="elements" :contents="contents"
          @update:contents="contents = $event; changed = true"
          @update:elements="elements = $event; changed = true"
          @update:files="files = $event; changed = true"
        />
      </v-window-item>

      <v-window-item value="preview">
        <PageDetailsPreview :item="item" />
      </v-window-item>

    </v-window>
  </v-main>

  <Aside v-model:state="nav" />
</template>

<style scoped>
  .v-app-bar-title {
    cursor: pointer;
  }

  .v-toolbar__content>.v-toolbar-title {
    margin-inline-start: 0;
  }

  .v-toolbar-title__placeholder {
    text-align: center;
  }

  .v-badge--dot .v-badge__badge {
    margin-inline-start: 0.5rem;
  }
</style>
