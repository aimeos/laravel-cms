<script>
  import gql from 'graphql-tag'
  import User from './User.vue'
  import Navigation from './Navigation.vue'
  import { useAppStore, useMessageStore } from '../stores'

  export default {
    components: {
      Navigation,
      User
    },

    props: {
      'grid': {type: Boolean, default: false},
      'mime': {type: [String, null], default: null},
      'nav': {type: Boolean, default: false}
    },

    emits: ['update:nav', 'update:item'],

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
        vgrid: false,
      }
    },

    setup() {
      const messages = useMessageStore()
      const app = useAppStore()

      return { app, messages }
    },

    created() {
      this.search()
      this.search = this.debounce(this.search, 500)
      this.vgrid = this.grid
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
      add(ev) {
        const promises = []
        const files = ev.target.files || ev.dataTransfer.files || []

        if(!files.length) {
          return
        }

        Array.from(files).forEach(file => {
          promises.push(this.$apollo.mutate({
            mutation: gql`mutation($file: Upload!) {
              addFile(file: $file) {
                id
                mime
                name
                path
                previews
                editor
                created_at
                updated_at
                deleted_at
              }
            }`,
            variables: {
              file: file
            },
            context: {
              hasUpload: true
            }
          }).then(response => {
            if(response.errors) {
              throw response.errors
            }

            const data = response.data?.addFile || {}
            data.previews = JSON.parse(data.previews) || {}

            this.items.unshift(data)
            return data
          }).catch(error => {
            console.error(`addFile()`, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.invalidate()
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


      drop(idx) {
        let list = []
        const promises = []

        if(!this.items[idx]) {
          list = this.items.filter(item => item._checked)
        } else {
          list.push(this.items[idx])
        }

        list.forEach(item => {
          promises.push(this.$apollo.mutate({
            mutation: gql`
              mutation($id: ID!) {
                dropFile(id: $id) {
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

            return result.data.dropFile
          }).catch(error => {
            this.messages.add('Error trashing file', 'error')
            console.error(`trash()`, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.invalidate()
          this.search(this.filter)
        })
      },


      invalidate() {
        const cache = this.$apollo.provider.defaultClient.cache
        cache.evict({id: 'ROOT_QUERY', fieldName: 'files'})
        cache.gc()
      },


      keep(idx) {
        let list = []
        const promises = []

        if(!this.items[idx]) {
          list = this.items.filter(item => item._checked)
        } else {
          list.push(this.items[idx])
        }

        list.forEach(item => {
          promises.push(this.$apollo.mutate({
            mutation: gql`
              mutation($id: ID!) {
                keepFile(id: $id) {
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
            this.messages.add('Error restoring file', 'error')
            console.error(`purge()`, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.invalidate()
          this.search(this.filter)
        })
      },


      purge(idx) {
        let list = []
        const promises = []

        if(!this.items[idx]) {
          list = this.items.filter(item => item._checked)
        } else {
          list.push(this.items[idx])
        }

        list.forEach(item => {
          promises.push(this.$apollo.mutate({
            mutation: gql`
              mutation($id: ID!) {
                purgeFile(id: $id) {
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

            return result.data.purgeFile
          }).catch(error => {
            this.messages.add('Error purging file', 'error')
            console.error(`purge()`, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.invalidate()
          this.search(this.filter)
        })
      },


      search(filter, page = 1, limit = 100) {
        this.loading = true

        return this.$apollo.query({
          query: gql`
            query($filter: FileFilter, $sort: [QueryFilesSortOrderByClause!], $limit: Int!, $page: Int!, $trashed: Trashed) {
              files(filter: $filter, sort: $sort, first: $limit, page: $page, trashed: $trashed) {
                data {
                  id
                  tag
                  name
                  mime
                  path
                  previews
                  editor
                  created_at
                  updated_at
                  deleted_at
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
              mime: this.mime,
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

          this.page = result.data.files.paginatorInfo.currentPage
          this.last = result.data.files.paginatorInfo.lastPage
          this.items = [...result.data.files.data || []].map(item => {
            return {
              ...item,
              previews: JSON.parse(item.previews || '{}'),
            }
          })
          this.checked = false
          this.loading = false
        }).catch(error => {
          this.messages.add('Error fetching files', 'error')
          console.error(`files()`, error)
        })
      },


      shown(item) {
        return true
      },


      srcset(map) {
        let list = []
        for(const key in map) {
          list.push(`${this.url(map[key])} ${key}w`)
        }
        return list.join(', ')
      },


      toggle() {
        this.items.forEach(el => {
          if(this.shown(el)) {
            el._checked = !el._checked
          }
        })
      },


      trashed(value) {
        this.trash = value
        this.search(this.filter)
      },


      url(path) {
        if(path.startsWith('http') || path.startsWith('blob:')) {
          return path
        }
        return this.app.urlfile.replace(/\/+$/g, '') + '/' + path
      },
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
  <v-app-bar :elevation="1" density="compact">
    <template #prepend>
      <v-btn @click.stop="$emit('update:nav', !nav)">
        <v-icon size="x-large">
          {{ `mdi-${nav ? 'close' : 'menu'}` }}
        </v-icon>
      </v-btn>
    </template>

    <v-app-bar-title>Files</v-app-bar-title>

    <template #append>
      <User />
    </template>
  </v-app-bar>

  <Navigation :state="nav" @update:state="$emit('update:nav', $event)" />

  <v-main class="file-list">
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
                <v-list-item>
                  <v-btn prepend-icon="mdi-folder-plus" variant="text" @click="$refs.upload.click()">Add files</v-btn>
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
            <v-btn v-if="!vgrid" @click="vgrid = true" icon="mdi-view-grid-outline" variant="flat" title="Grid view"></v-btn>
            <v-btn v-if="vgrid" @click="vgrid = false" icon="mdi-format-list-bulleted-square" variant="flat" title="List view"></v-btn>

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
                  <v-btn variant="text" @click="sort = {column: 'MIME', order: 'ASC'}">Mime</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn variant="text" @click="sort = {column: 'TAG', order: 'ASC'}">Tag</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn variant="text" @click="sort = {column: 'EDITOR', order: 'ASC'}">Editor</v-btn>
                </v-list-item>
              </v-list>
            </v-menu>
          </div>
        </div>

        <v-list class="items" :class="{grid: vgrid}">
          <v-list-item v-for="(item, idx) in items" :key="idx" :class="{trashed: item.deleted_at}">
            <v-checkbox-btn v-model="item._checked" @click.stop="" class="item-check"></v-checkbox-btn>

            <v-menu>
              <template v-slot:activator="{ props }">
                <v-btn class="item-menu" icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
              </template>
              <v-list>
                <v-list-item v-if="!item.deleted_at">
                  <v-btn prepend-icon="mdi-delete" variant="text" @click="drop(idx)">Trash</v-btn>
                </v-list-item>
                <v-list-item v-if="item.deleted_at">
                  <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep(idx)">Restore</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge(idx)">Purge</v-btn>
                </v-list-item>
              </v-list>
            </v-menu>

            <div class="item-preview" @click="$emit('update:item', item)">
              <v-img v-if="item.previews"
                :src="url(item.path)"
                :srcset="srcset(item.previews)"
                :alt="item.name"
              ></v-img>
            </div>

            <div class="item-data" @click="$emit('update:item', item)">
              <div class="item-title">
                <v-list-item-title>{{ item.name }}</v-list-item-title>
                <v-list-item-subtitle>{{ item.mime }}</v-list-item-subtitle>
              </div>

              <div class="item-meta">
                <div class="item-editor">{{ item.editor }}</div>
                <v-list-item-subtitle class="item-modified">{{ item.updated_at }}</v-list-item-subtitle>
              </div>
            </div>

            <v-btn class="item-open" icon="mdi-arrow-right" variant="text" @click="$emit('update:item', item)"></v-btn>
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
          <input @change="add($event)"
            ref="upload"
            type="file"
            multiple
            hidden
          />
          <v-btn color="primary" icon="mdi-folder-plus" @click="$refs.upload.click()"></v-btn>
        </div>
      </v-sheet>
    </v-container>
  </v-main>
</template>

<style scoped>
  .file-list .box {
    margin: 1rem 0;
  }

  .header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 1rem;
  }

  .header > * {
    margin: 0.5rem 0;
  }

  .bulk {
    display: flex;
    align-items: center;
  }

  .search {
    display: flex;
    flex-grow: 1;
    width: 100%;
    margin: auto;
    order: 3;
}

  .search .v-select {
    max-width: 10rem;
    margin: 0 0.5rem;
  }

  .search .v-text-field {
    min-width: 7.5rem;
    margin: 0 0.5rem;
  }

  .trashed .item-data {
    text-decoration: line-through;
  }

  .loading,
  .notfound {
    display: flex;
    align-items: center;
  }

  .loading .spinner {
    margin-inline-start: 16px;
    color: #808080;
  }

  .items:not(.grid) .v-list-item {
    border-bottom: 1px solid rgb(var(--v-theme-primary));
    padding: 0.5rem 0;
  }

  .items:not(.grid) .v-list-item > * {
    display: flex;
    align-items: center;
  }

  .items:not(.grid) .v-selection-control {
    flex-grow: unset;
  }

  .items:not(.grid) .item-preview .v-img {
    margin-inline-start: 8px;
    margin-inline-end: 16px;
    cursor: pointer;
    display: none;
    height: 48px;
    width: 72px
  }

  .items:not(.grid) .item-data {
    justify-content: space-between;
    flex-wrap: wrap;
    display: flex;
    flex-grow: 1;
    cursor: pointer;
  }

  .items.grid {
    grid-template-columns: repeat(auto-fill, minmax(270px, 1fr));
    display: grid;
    gap: 16px;
  }

  .items.grid .v-list-item {
    border: 1px solid rgb(var(--v-theme-primary));
  }

  .items.grid .v-list-item .item-check,
  .items.grid .v-list-item .item-menu {
    background: rgb(var(--v-theme-surface-variant));
    color: rgb(var(--v-theme-surface));
    opacity: 0.66;
    border-radius: 50%;
    position: absolute;
    display: none;
    z-index: 2;
    top: 0;
  }

  .items.grid .v-list-item:hover .item-menu,
  .items.grid .v-list-item:hover .item-check,
  .items.grid .v-list-item .item-check:has(input:checked) {
    display: block;
  }

  .items.grid .v-list-item .item-check {
    left: 0;
  }

  .items.grid .v-list-item .item-menu {
    right: 0;
  }

  .items.grid .item-preview {
    display: flex;
    height: 180px;
    z-index: 1;
  }

  .items.grid .item-preview .v-img {
    display: block;
  }

  .items.grid .item-open {
    display: none;
  }

  @media (min-width: 500px) {
    .items:not(.grid) .item-preview .v-img {
      display: block;
    }
  }

  @media (min-width: 600px) {
    .search {
      order: unset;
      width: unset;
      max-width: 30rem;
    }
  }
</style>
