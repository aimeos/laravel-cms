<script>
  import gql from 'graphql-tag'
  import { useAuthStore, useMessageStore } from '../stores'

  export default {
    emits: ['update:item'],

    data() {
      return {
        items: [],
        filter: '',
        sort: {column: 'ID', order: 'DESC'},
        page: 1,
        last: 1,
        limit: 100,
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
      this.search = this.debounce(this.search, 500)
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
      add() {
        if(!this.auth.can('element:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        this.$apollo.mutate({
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
              type: 'text',
              name: 'New Element',
            }
          }
        }).then(response => {
          if(response.errors) {
            throw response.errors
          }

          const data = response.data?.addElement || {}
          data.data = JSON.parse(data.data) || {}

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

        let list = []
        const promises = []

        if(!item) {
          list = this.items.filter(item => item._checked)
        } else {
          list.push(item)
        }

        list.forEach(item => {
          promises.push(this.$apollo.mutate({
            mutation: gql`
              mutation($id: ID!) {
                dropElement(id: $id) {
                  id
                }
              }
            `,
            variables: {
              id: item.id
            },
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            return result.data.dropElement
          }).catch(error => {
            this.messages.add('Error trashing shared element', 'error')
            this.$log(`ElementListItems::drop(): Error trashing shared element`, item, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.invalidate()
          this.search(this.filter)
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

        let list = []
        const promises = []

        if(!item) {
          list = this.items.filter(item => item._checked)
        } else {
          list.push(item)
        }

        list.forEach(item => {
          promises.push(this.$apollo.mutate({
            mutation: gql`
              mutation($id: ID!) {
                keepElement(id: $id) {
                  id
                }
              }
            `,
            variables: {
              id: item.id
            },
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            item.deleted_at = null
            return result.data.keepFile
          }).catch(error => {
            this.messages.add('Error restoring shared element', 'error')
            this.$log(`ElementListItems::keep(): Error restoring shared element`, item, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.invalidate()
          this.search(this.filter)
        })
      },


      publishAll() {
        const list = this.items.filter(item => {
          return item._checked && item.id && !item.published
        })

        list.reverse().forEach(item => {
          this.publish(item)
        })
      },


      publish(item) {
        if(!this.auth.can('element:publish')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        if(item.published) {
          return
        }

        this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!) {
              pubElement(id: $id) {
                id
              }
            }`,
            variables: {
              id: item.id
            }
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            item.published = true
            item._checked = false
          }).catch(error => {
            this.messages.add('Error publishing shared element', 'error')
            this.$log(`ElementListItems::publish(): Error publishing shared element`, item, error)
          })
      },


      purge(item) {
        if(!this.auth.can('element:purge')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        let list = []
        const promises = []

        if(!item) {
          list = this.items.filter(item => item._checked)
        } else {
          list.push(item)
        }

        list.forEach(item => {
          promises.push(this.$apollo.mutate({
            mutation: gql`
              mutation($id: ID!) {
                purgeElement(id: $id) {
                  id
                }
              }
            `,
            variables: {
              id: item.id
            },
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            return result.data.purgeElement
          }).catch(error => {
            this.messages.add('Error purging shared element', 'error')
            this.$log(`ElementListItems::purge(): Error purging shared element`, item, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.invalidate()
          this.search(this.filter)
        })
      },


      search(filter, page = 1, limit = 100) {
        if(!this.auth.can('element:view')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve([])
        }

        this.loading = true

        return this.$apollo.query({
          query: gql`
            query($filter: ElementsFilter, $sort: [QueryElementsSortOrderByClause!], $limit: Int!, $page: Int!, $trashed: Trashed) {
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
                  currentPage
                  lastPage
                }
              }
            }
          `,
          variables: {
            filter: {
              any: filter,
            },
            page: this.page,
            limit: this.limit,
            sort: [this.sort],
            trashed: this.trash === null ? 'WITH' : (this.trash ? 'ONLY' : 'WITHOUT')
          },
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          const elements = result.data.elements || {}

          this.last = elements.paginatorInfo?.lastPage || 1
          this.page = elements.paginatorInfo?.currentPage || 1
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
      },


      trashed(value) {
        this.trash = value
        this.search(this.filter)
      }
    },

    watch: {
      filter(value) {
        this.search(value)
      },


      page(value) {
        this.search(this.filter, value)
      },


      sort(value) {
        this.search(this.filter)
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
              <v-list-item v-show="isChecked">
                <v-btn prepend-icon="mdi-publish" variant="text" @click="publishAll()">Publish</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn prepend-icon="mdi-folder-plus" variant="text" @click="add()">Add element</v-btn>
              </v-list-item>
              <v-list-item v-show="trash !== false">
                <v-btn prepend-icon="mdi-delete-off" variant="text" @click="trashed(false)">Only non-trashed</v-btn>
              </v-list-item>
              <v-list-item v-show="trash !== null">
                <v-btn prepend-icon="mdi-delete-circle-outline" variant="text" @click="trashed(null)">Include trashed</v-btn>
              </v-list-item>
              <v-list-item v-show="trash !== true">
                <v-btn prepend-icon="mdi-delete-circle" variant="text" @click="trashed(true)">Only trashed</v-btn>
              </v-list-item>
              <v-list-item v-show="canTrash">
                <v-btn prepend-icon="mdi-delete" variant="text" @click="drop()">Trash</v-btn>
              </v-list-item>
              <v-list-item v-show="isTrashed">
                <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep()">Restore</v-btn>
              </v-list-item>
              <v-list-item v-show="isChecked">
                <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge()">Purge</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>

        <div class="search">
          <v-text-field
            v-model="filter"
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
              <v-list-item v-show="!item.deleted_at && !item.published">
                <v-btn prepend-icon="mdi-publish" variant="text" @click="publish(item)">Publish</v-btn>
              </v-list-item>
              <v-list-item v-if="!item.deleted_at">
                <v-btn prepend-icon="mdi-delete" variant="text" @click="drop(item)">Trash</v-btn>
              </v-list-item>
              <v-list-item v-if="item.deleted_at">
                <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep(item)">Restore</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge(item)">Purge</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>

          <div class="item-content" @click="$emit('update:item', item)" :class="{trashed: item.deleted_at}":title="title(item)">
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
      <p v-if="!loading && filter && !items.length" class="notfound">
        No files found
      </p>

      <v-pagination v-if="last > 1"
        v-model="page"
        :length="last"
      ></v-pagination>

      <div class="btn-group">
        <v-btn color="primary" icon="mdi-folder-plus" @click="add()"></v-btn>
      </div>
    </v-sheet>
  </v-container>
</template>

<style scoped>
  .items.list .v-list-item {
    border-bottom: 1px solid rgb(var(--v-theme-primary));
    padding: 0.5rem 0;
  }

  .items.list .v-list-item > * {
    display: flex;
    align-items: center;
  }

  .items.list .v-selection-control {
    flex-grow: unset;
  }
</style>
