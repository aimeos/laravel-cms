<script>
  import gql from 'graphql-tag'
  import SchemaItems from './SchemaItems.vue'
  import { useAuthStore, useMessageStore } from '../stores'

  export default {
    components: {
      SchemaItems
    },

    props: {
      'embed': {type: Boolean, default: false},
      'filter': {type: Object, default: () => ({})},
    },

    emits: ['select'],

    data() {
      return {
        items: [],
        term: '',
        sort: {column: 'ID', order: 'DESC'},
        page: 1,
        last: 1,
        limit: 100,
        vschemas: false,
        checked: false,
        loading: true,
        trash: false,
      }
    },

    setup() {
      const messages = useMessageStore()
      const auth = useAuthStore()

      return { auth, messages }
    },

    created() {
      this.search()
      this.searchd = this.debounce(this.search, 500)
    },

    computed: {
      canTrash() {
        return this.items.some(item => item._checked && !item.deleted_at)
      },

      isChecked() {
        return this.items.some(item => item._checked)
      },

      isTrashed() {
        return this.items.some(item => item._checked && item.deleted_at)
      },
    },

    methods: {
      add(item) {
        if(this.embed || !this.auth.can('element:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        return this.$apollo.mutate({
          mutation: gql`mutation($input: ElementInput!) {
            addElement(input: $input) {
              id
              lang
              name
              type
              data
              editor
              created_at
              updated_at
              deleted_at
            }
          }`,
          variables: {
            input: {
              type: item.type,
              name: 'New shared element',
              data: '{}',
            }
          }
        }).then(response => {
          if(response.errors) {
            throw response.errors
          }

          const data = response.data?.addElement || {}
          data.data = JSON.parse(data.data) || {}
          data.published = true

          this.vschemas = false
          this.items.unshift(data)

          this.$emit('select', data)
          this.invalidate()

          return data
        }).catch(error => {
          this.$log(`ElementListItems::add(): Error adding shared element`, error)
        })
      },


      debounce(func, delay) {
        let timer

        return function(...args) {
          return new Promise((resolve, reject) => {
            const context = this

            clearTimeout(timer)
            timer = setTimeout(() => {
              try {
                resolve(func.apply(context, args))
              } catch (error) {
                reject(error)
              }
            }, delay)
          })
        }
      },


      drop(item) {
        if(!this.auth.can('element:drop')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const list = item ? [item] : this.items.filter(item => item._checked)

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`
            mutation($id: [ID!]!) {
              dropElement(id: $id) {
                id
              }
            }
          `,
          variables: {
            id: list.map(item => item.id)
          },
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          this.invalidate()
          this.search()
        }).catch(error => {
          this.messages.add('Error trashing shared element', 'error')
          this.$log(`ElementListItems::drop(): Error trashing shared element`, list, error)
        })
      },


      invalidate() {
        const cache = this.$apollo.provider.defaultClient.cache
        cache.evict({id: 'ROOT_QUERY', fieldName: 'elements'})
        cache.gc()
      },


      keep(item) {
        if(!this.auth.can('element:keep')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const list = item ? [item] : this.items.filter(item => item._checked)

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`
            mutation($id: [ID!]!) {
              keepElement(id: $id) {
                id
              }
            }
          `,
          variables: {
            id: list.map(item => item.id)
          },
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          list.forEach(item => {
            item.deleted_at = null
          })

          this.invalidate()
          this.search()
        }).catch(error => {
          this.messages.add('Error restoring shared element', 'error')
          this.$log(`ElementListItems::keep(): Error restoring shared element`, list, error)
        })
      },


      publish(item) {
        if(!this.auth.can('element:publish')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const list = item ? [item] : this.items.filter(item => {
          return item._checked && item.id && !item.published
        })

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: [ID!]!) {
            pubElement(id: $id) {
              id
            }
          }`,
          variables: {
            id: list.map(item => item.id)
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          list.forEach(item => {
            item.published = true
            item._checked = false
          })
        }).catch(error => {
          this.messages.add('Error publishing shared element', 'error')
          this.$log(`ElementListItems::publish(): Error publishing shared element`, list, error)
        })
      },


      purge(item) {
        if(!this.auth.can('element:purge')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const list = item ? [item] : this.items.filter(item => item._checked)

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`
            mutation($id: [ID!]!) {
              purgeElement(id: $id) {
                id
              }
            }
          `,
          variables: {
            id: list.map(item => item.id)
          },
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          this.invalidate()
          this.search()
        }).catch(error => {
          this.messages.add('Error purging shared element', 'error')
          this.$log(`ElementListItems::purge(): Error purging shared element`, list, error)
        })
      },


      search() {
        if(!this.auth.can('element:view')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve([])
        }

        const filter = {
          any: this.term
        }

        if(this.filter.editor) {
          filter.editor = this.filter.editor
        }

        this.loading = true

        return this.$apollo.query({
          query: gql`
            query($filter: ElementFilter, $sort: [QueryElementsSortOrderByClause!], $limit: Int!, $page: Int!, $trashed: Trashed) {
              elements(filter: $filter, sort: $sort, first: $limit, page: $page, trashed: $trashed) {
                data {
                  id
                  lang
                  name
                  type
                  data
                  editor
                  created_at
                  updated_at
                  deleted_at
                  latest {
                    id
                    published
                    publish_at
                    data
                    editor
                    created_at
                  }
                }
                paginatorInfo {
                  lastPage
                }
              }
            }
          `,
          variables: {
            filter: filter,
            page: this.page,
            limit: this.limit,
            sort: [this.sort],
            trashed: this.filter.trashed || 'WITHOUT'
          },
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          const elements = result.data.elements || {}

          this.last = elements.paginatorInfo?.lastPage || 1
          this.items = [...elements.data || []].map(entry => {
            const item = entry.latest?.data ? JSON.parse(entry.latest?.data) : {
              ...entry,
              data: JSON.parse(entry.data || '{}')
            }

            return Object.assign(item, {
              id: entry.id,
              deleted_at: entry.deleted_at,
              created_at: entry.created_at,
              updated_at: entry.latest?.created_at || entry.updated_at,
              editor: entry.latest?.editor || entry.editor,
              published: entry.latest?.published ?? true,
              publish_at: entry.latest?.publish_at || null,
              latest: entry.latest,
            })
          })
          this.checked = false
          this.loading = false

          return this.items
        }).catch(error => {
          this.messages.add('Error fetching shared elements', 'error')
          this.$log(`ElementListItems::search(): Error fetching shared element`, error)
        })
      },


      title(item) {
        const list = []

        if(item.publish_at) {
          list.push('Publish at: ' + (new Date(item.publish_at)).toLocaleDateString())
        }

        return list.join("\n")
      },


      toggle() {
        this.items.forEach(el => {
          el._checked = !el._checked
        })
      }
    },

    watch: {
      filter: {
        deep: true,
        handler() {
          this.search()
        }
      },


      term() {
        this.searchd()
      },


      page() {
        this.search()
      },


      sort() {
        this.search()
      }
    }
  }
</script>

<template>
  <v-container>
    <v-sheet class="box">
      <div class="header">
        <div class="bulk">
          <v-checkbox-btn v-model="checked" @click.stop="toggle()"></v-checkbox-btn>
          <v-menu location="bottom left">
            <template #activator="{ props }">
              <v-btn append-icon="mdi-menu-down" variant="outlined" v-bind="props">Actions</v-btn>
            </template>
            <v-list>
              <v-list-item v-show="isChecked && auth.can('element:publish')">
                <v-btn prepend-icon="mdi-publish" variant="text" @click="publish()">Publish</v-btn>
              </v-list-item>
              <v-list-item v-if="!this.embed && auth.can('element:add')">
                <v-btn prepend-icon="mdi-folder-plus" variant="text" @click="vschemas = true">Add element</v-btn>
              </v-list-item>
              <v-list-item v-show="canTrash && auth.can('element:drop')">
                <v-btn prepend-icon="mdi-delete" variant="text" @click="drop()">Trash</v-btn>
              </v-list-item>
              <v-list-item v-show="isTrashed && auth.can('element:keep')">
                <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep()">Restore</v-btn>
              </v-list-item>
              <v-list-item v-show="isChecked && auth.can('element:purge')">
                <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge()">Purge</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>

        <div class="search">
          <v-text-field
            v-model="term"
            prepend-inner-icon="mdi-magnify"
            variant="underlined"
            label="Search for"
            hide-details
            clearable
          ></v-text-field>
        </div>

        <div class="layout">
          <v-menu>
            <template #activator="{ props }">
              <v-btn append-icon="mdi-menu-down" prepend-icon="mdi-sort" variant="outlined" location="bottom right" v-bind="props">
                {{ sort?.column === 'ID' ? (sort?.order === 'DESC' ? 'Latest' : 'Oldest' ) : (sort?.column || '') }}
              </v-btn>
            </template>
            <v-list>
              <v-list-item>
                <v-btn variant="text" @click="sort = {column: 'ID', order: 'DESC'}">Latest</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn variant="text" @click="sort = {column: 'ID', order: 'ASC'}">Oldest</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn variant="text" @click="sort = {column: 'NAME', order: 'ASC'}">Name</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn variant="text" @click="sort = {column: 'TYPE', order: 'ASC'}">Type</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn variant="text" @click="sort = {column: 'EDITOR', order: 'ASC'}">Editor</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>
      </div>

      <v-list class="items">
        <v-list-item v-for="(item, idx) in items" :key="idx">
          <v-checkbox-btn v-model="item._checked" :class="{draft: !item.published}" class="item-check"></v-checkbox-btn>

          <v-menu>
            <template v-slot:activator="{ props }">
              <v-btn class="item-menu" icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
            </template>
            <v-list>
              <v-list-item v-show="!item.deleted_at && !item.published && this.auth.can('element:publish')">
                <v-btn prepend-icon="mdi-publish" variant="text" @click="publish(item)">Publish</v-btn>
              </v-list-item>
              <v-list-item v-if="!item.deleted_at && this.auth.can('element:drop')">
                <v-btn prepend-icon="mdi-delete" variant="text" @click="drop(item)">Trash</v-btn>
              </v-list-item>
              <v-list-item v-if="item.deleted_at && this.auth.can('element:keep')">
                <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep(item)">Restore</v-btn>
              </v-list-item>
              <v-list-item v-if="this.auth.can('element:purge')">
                <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge(item)">Purge</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>

          <div class="item-content" @click="$emit('select', item)" :class="{trashed: item.deleted_at}":title="title(item)">
            <div class="item-text">
              <v-icon v-if="item.publish_at" class="publish-at" icon="mdi-clock-outline"></v-icon>
              <span class="item-lang" v-if="item.lang">{{ item.lang }}</span>
              <span class="item-title">{{ item.name }}</span>
              <div class="item-type item-subtitle">{{ item.type }}</div>
            </div>

            <div class="item-aux">
              <div class="item-editor">{{ item.editor }}</div>
              <div class="item-modified item-subtitle">{{ (new Date(item.updated_at)).toLocaleString() }}</div>
            </div>
          </div>
        </v-list-item>
      </v-list>

      <p v-if="loading" class="loading">
        Loading
        <svg class="spinner" width="32" height="32" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle class="spin1" cx="4" cy="12" r="3"/><circle class="spin1 spin2" cx="12" cy="12" r="3"/><circle class="spin1 spin3" cx="20" cy="12" r="3"/></svg>
      </p>
      <p v-if="!loading && !items.length" class="notfound">
        No items found
      </p>

      <v-pagination v-if="last > 1"
        v-model="page"
        :length="last"
      ></v-pagination>

      <div v-if="!this.embed && this.auth.can('element:add')" class="btn-group">
        <v-btn @click="vschemas = true"
          icon="mdi-view-grid-plus"
          color="primary"
          elevation="0"
        ></v-btn>
      </div>
    </v-sheet>
  </v-container>

  <Teleport to="body">
    <v-dialog v-model="vschemas" scrollable width="auto">
      <SchemaItems type="content" @add="add($event)" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
  .items .v-list-item {
    border-bottom: 1px solid rgb(var(--v-theme-primary));
    padding: 0.5rem 0;
  }

  .items .v-list-item > * {
    display: flex;
    align-items: center;
  }

  .items .v-selection-control {
    flex-grow: unset;
  }
</style>
