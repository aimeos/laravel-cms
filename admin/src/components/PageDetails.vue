<script>
  import gql from 'graphql-tag'
  import Aside from '../components/Aside.vue'
  import PageDetailsPage from '../components/PageDetailsPage.vue'
  import PageDetailsContent from '../components/PageDetailsContent.vue'
  import PageDetailsPreview from '../components/PageDetailsPreview.vue'


  export default {
    components: {
      Aside,
      PageDetailsPage,
      PageDetailsContent,
      PageDetailsPreview
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      tab: 'page',
      nav: null,
      page: {},
    }),
    watch: {
      item() {
        if(this.page.id && this.item.id === this.page.id) {
          return
        }

        this.$apollo.query({
          query: gql`query($id: ID!) {
            page(id: $id) {
              id
              parent_id
              domain
              slug
              lang
              name
              title
              to
              tag
              status
              cache
              data
              config
              start
              end
              editor
              created_at
              updated_at
              deleted_at
              has
              latest {
                published
                data
              }
              contents {
                id
                data
                ref {
                  position
                  status
                  start
                  end
                }
                latest {
                  published
                  data
                }
              }
            }
          }`,
          variables: {
            id: this.item.id
          }
        }).then(result => {
          if(!result.errors && result.data) {
            this.page = Object.assign(this.page, result.data.page);
          } else {
            console.log(`page(id: ${this.item.id})`, result)
          }
        }).catch(error => {
          console.log(`page(id: ${this.item.id})`, error)
        })
      }
    }
  }
</script>

<template>
  <v-app-bar :elevation="2" density="compact" image="src/assets/background.png">
    <v-app-bar-title>
      <div class="app-title" @click="$emit('update:item', page)">
        <v-icon icon="mdi-keyboard-backspace"></v-icon> Back to pages
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
      <v-tab value="content" class="unpublished">Content</v-tab>
      <v-tab value="preview">Preview</v-tab>
    </v-tabs>

    <v-window v-model="tab">

      <v-window-item value="page">
        <PageDetailsPage :item="page" @update:item="page = $event" />
      </v-window-item>

      <v-window-item value="content">
        <PageDetailsContent :item="page" @update:item="page = $event"  />
      </v-window-item>

      <v-window-item value="preview">
        <PageDetailsPreview :item="page" @update:item="page = $event"  />
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

  .unpublished {
    color: rgb(var(--v-theme-warning));
  }
</style>
