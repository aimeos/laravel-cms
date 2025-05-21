<script>
  import gql from 'graphql-tag'
  import { Draggable } from '@he-tree/vue'
  import { dragContext } from '@he-tree/vue'
  import { useAppStore, useLanguageStore, useMessageStore } from '../stores'
  import Navigation from './Navigation.vue'

  export default {
    components: {
      Draggable,
      Navigation
    },

    props: {
      'item': {type: Object, required: true},
      'nav': {type: Boolean, default: false}
    },

    emits: ['update:nav', 'update:item'],

    data() {
      return {
        clip: null,
        menu: {},
        pages: [],
        trash: false,
        loading: true,
        filter: '',
      }
    },

    setup() {
      const languages = useLanguageStore()
      const messages = useMessageStore()
      const app = useAppStore()

      return { app, languages, messages }
    },

    created() {
      this.fetch().then(result => {
        this.pages = result.data
        this.loading = false
      })
    },

    computed: {
      canTrash() {
        return this.$refs.tree.statsFlat.some(stat => stat.check && !stat.data.deleted_at)
      },

      isChecked() {
        return this.$refs.tree.statsFlat.some(stat => stat.check)
      },

      isTrashed() {
        return this.$refs.tree.statsFlat.some(stat => stat.check && stat.data.deleted_at)
      },
    },

    methods: {
      add() {
        const node = this.create()

        this.$apollo.mutate({
          mutation: gql`mutation ($input: PageInput!) {
            addPage(input: $input) {
              id
            }
          }`,
          variables: {
            input: node
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          node.id = result.data.addPage.id
          this.$refs.tree.add(node)
        }).catch(error => {
          this.messages.add('Error adding root page', 'error')
          console.error(`addPage(root)`, error)
        })
      },


      change() {
        const parent = dragContext.targetInfo.parent
        const siblings = dragContext.targetInfo.siblings
        const ref = siblings[dragContext.targetInfo.indexBeforeDrop+1] || null

        this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $parent: ID, $ref: ID) {
            movePage(id: $id, parent: $parent, ref: $ref) {
              id
            }
          }`,
          variables: {
            id: dragContext.startInfo.dragNode.data.id,
            parent: parent ? parent.data.id : null,
            ref: ref ? ref.data.id : null
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          const srcparent = dragContext.startInfo.parent

          if(!srcparent?.children.length) {
            srcparent.data.has = false
          }

          if(parent) {
            parent.data.has = true
          }
        }).catch(error => {
          this.messages.add('Error moving page', 'error')
          console.error(`movePage(id: ${dragContext.startInfo.dragNode.data.id})`, error)
        })
      },


      copy(stat, node) {
        this.clip = {type: 'copy', node: node, stat: stat}
      },


      create(attr = {}) {
        return Object.assign({
          slug: '_' + Math.floor(Math.random() * 10000),
          lang: this.languages.current,
          status: 0,
          cache: 5
        }, attr)
      },


      cut(stat, node) {
        this.clip = {type: 'cut', node: node, stat: stat}
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


      drop(stat) {
        const list = stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id
        })

        list.forEach(stat => {
          this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!) {
              dropPage(id: $id) {
                id
              }
            }`,
            variables: {
              id: stat.data.id
            }
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            this.update(stat, (stat) => {
              stat.data.deleted_at = (new Date).toISOString().replace(/T/, ' ').substring(0, 19)
              stat.check = false
            })

            if(stat.parent && !stat.parent.children?.length) {
              stat.parent.data.has = false
            }
          }).catch(error => {
            this.messages.add('Error trashing page', 'error')
            console.error(`dropPage(id: ${stat.data.id})`, error)
          })
        })
      },


      fetch(parent = null, page = 1, limit = 100) {
        return this.$apollo.query({
          query: gql`query($filter: PageFilter, $limit: Int!, $page: Int!, $trashed: Trashed) {
            pages(filter: $filter, first: $limit, page: $page, trashed: $trashed) {
              data {
                ${this.fields()}
              }
              paginatorInfo {
                currentPage
                lastPage
              }
            }
          }`,
          variables: {
            filter: {
              parent_id: parent,
              lang: this.languages.current,
            },
            page: page,
            limit: limit,
            trashed: this.trash === null ? 'WITH' : (this.trash ? 'ONLY' : 'WITHOUT')
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          return this.transform(result.data.pages)
        }).catch(error => {
          this.messages.add('Error fetching pages', 'error')
          console.error(`pages()`, error)
        })
      },


      fields() {
        return `id
          parent_id
          lang
          slug
          domain
          name
          title
          to
          tag
          type
          theme
          meta
          config
          status
          cache
          editor
          updated_at
          deleted_at
          has
          latest {
            published
            data
            editor
            created_at
          }`
      },


      init(stat) {
        if(stat.data.deleted_at && this.trash === false ) {
          stat.hidden = true
        }

        return stat
      },


      insert(stat, idx = null) {
        const siblings = this.$refs.tree.getSiblings(stat)
        const parent = idx !== null ? stat.parent : stat
        const pos = siblings.indexOf(stat)
        const node = this.create()
        let refid = null

        if(idx === null && !stat.open) {
          this.load(stat, stat.data)
        }

        switch(idx) {
          case 0: refid = stat.data.id; break
          case null: refid = stat.children && stat.children[0] ? stat.children[0].data.id : null; break
          case 1: refid = siblings[pos + 1] ? siblings[pos + 1].data.id : null; break
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($input: PageInput!, $parent: ID, $ref: ID) {
            addPage(input: $input, parent: $parent, ref: $ref) {
              id
            }
          }`,
          variables: {
            input: node,
            parent: parent ? parent.data.id : null,
            ref: refid
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          node.id = result.data.addPage.id

          if(idx !== null || stat.open) {
            this.$refs.tree.add(node, parent, idx !== null ? pos + idx : 0)
          }

          parent.data.has = true
        }).catch(error => {
          this.messages.add('Error inserting page', 'error')
          console.error(`addPage(insert)`, error)
        })
      },


      keep(stat) {
        const stats = stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id && stat.data.deleted_at
        })
        const list = stats.filter(stat => {
          return stats.indexOf(stat.parent) === -1
        })

        list.forEach(stat => {
          this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!) {
              keepPage(id: $id) {
                id
              }
            }`,
            variables: {
              id: stat.data.id
            }
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            const deleted_at = stat.data.deleted_at

            this.update(stat, (stat) => {
              if(deleted_at >= stat.data.deleted_at) {
                stat.data.deleted_at = null
                stat.check = false
              }
            })
          }).catch(error => {
            this.messages.add('Error restoring page', 'error')
            console.error(`keepPage(id: ${stat.data.id})`, error)
          })
        })
      },


      load(stat, node) {
        if(!stat.open && !node.children) {
          stat.loading = true

          this.fetch(node.id, stat.page ? stat.page + 1 : 1).then(result => {
            this.$refs.tree.addMulti(result.data, stat, 0)
            stat.page = result.currentPage || 1
          }).finally(() => {
            stat.loading = false
          })
        }

        stat.open = !stat.open
      },


      move(stat, idx = null) {
        const siblings = this.$refs.tree.getSiblings(stat)
        const parent = idx !== null ? stat.parent : stat
        const pos = siblings.indexOf(stat)
        let refid = null

        switch(idx) {
          case 0: refid = stat.data.id; break
          case null: refid = stat.children && stat.children[0] ? stat.children[0].data.id : null; break
          case 1: refid = siblings[pos + 1] ? siblings[pos + 1].data.id : null; break
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $parent: ID, $ref: ID) {
            movePage(id: $id, parent: $parent, ref: $ref) {
              id
            }
          }`,
          variables: {
            id: this.clip.node.id,
            parent: parent ? parent.data.id : null,
            ref: refid
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          const index = idx !== null ? (pos < this.$refs.tree.getSiblings(stat).indexOf(this.clip.stat) ? pos + idx : pos) : 0

          this.$refs.tree.move(this.clip.stat, parent, index)

          if(!this.clip.stat.children?.length) {
            stat.parent.data.has = false
          }
          parent.data.has = true
        }).catch(error => {
          this.messages.add('Error moving page', 'error')
          console.error(`movePage(id: ${this.clip.node.id})`, error)
        })

        this.show()
      },


      paste(stat, idx = null) {
        const siblings = this.$refs.tree.getSiblings(stat)
        const parent = idx !== null ? stat.parent : stat
        const pos = siblings.indexOf(stat)
        const node = {...this.clip.node}
        let refid = null

        node.slug = node.slug + '_' + Math.floor(Math.random() * 10000)
        node.status = 0
        node.children = null
        node.has = false
        node.id = null

        switch(idx) {
          case 0: refid = stat.data.id; break
          case null: refid = stat.children && stat.children[0] ? stat.children[0].data.id : null; break
          case 1: refid = siblings[pos + 1] ? siblings[pos + 1].data.id : null; break
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $parent: ID, $ref: ID) {
            addPage(id: $id, parent: $parent, ref: $ref) {
              id
            }
          }`,
          variables: {
            id: this.clip.node.id,
            parent: parent ? parent.data.id : null,
            ref: refid
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          const index = idx !== null ? this.$refs.tree.getSiblings(stat).indexOf(stat) + idx : 0

          this.$refs.tree.add(node, parent, index)
          parent.data.has = true
        }).catch(error => {
          this.messages.add('Error copying page', 'error')
          console.error(`addPage(id: ${this.clip.node.id})`, error)
        })

        this.show()
      },


      publishAll() {
        const list = this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id && !stat.data.published
        })

        list.reverse().forEach(stat => {
          this.publish(stat)
        })
      },


      publish(stat) {
        if(stat.published) {
          return
        }

        this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!) {
              pubPage(id: $id) {
                id
              }
            }`,
            variables: {
              id: stat.data.id
            }
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            stat.data.published = true
            stat.check = false
          }).catch(error => {
            this.messages.add('Error publishing page', 'error')
            console.error(`pubPage(id: ${stat.data.id})`, error)
          })
      },


      purge(stat) {
        const list = stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id
        })

        list.reverse().forEach(stat => {
          this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!) {
              purgePage(id: $id) {
                id
              }
            }`,
            variables: {
              id: stat.data.id
            }
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            this.$refs.tree.remove(stat)

            if(stat.parent && !stat.parent.children?.length) {
              stat.parent.data.has = false
            }
          }).catch(error => {
            this.messages.add('Error purging page', 'error')
            console.error(`purgePage(id: ${stat.data.id})`, error)
          })
        })
      },


      reload(lang = null) {
        this.loading = true
        this.languages.current = lang

        this.fetch().then(result => {
          this.pages = result.data
        }).finally(() => {
          this.loading = false
        })
      },


      search(filter, page = 1, limit = 100) {
        return this.$apollo.query({
          query: gql`query($filter: PageFilter, $limit: Int!, $page: Int!, $trashed: Trashed) {
            pages(filter: $filter, first: $limit, page: $page, trashed: $trashed) {
              data {
                ${this.fields()}
              }
              paginatorInfo {
                currentPage
                lastPage
              }
            }
          }`,
          variables: {
            filter: {
              lang: this.languages.current,
              any: filter
            },
            page: page,
            limit: limit,
            trashed: this.trash === null ? 'WITH' : (this.trash ? 'ONLY' : 'WITHOUT')
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          return this.transform(result.data.pages)
        }).catch(error => {
          this.messages.add('Error searching for pages', 'error')
          console.error(`pages()`, error)
        })
      },


      show(what = null) {
        if(what === null) {
          this.menu = {}
        } else if(!this.menu[what]) {
          this.menu = {}
          this.menu[what] = true
        } else {
          this.menu[what] = false
        }
      },


      status(stat, val) {
        const list = stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id
        })

        list.forEach(stat => {
          this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!, $input: PageInput!) {
              savePage(id: $id, input: $input) {
                id
              }
            }`,
            variables: {
              id: stat.data.id,
              input: {
                status: val
              }
            }
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            stat.data.status = val
          }).catch(error => {
            this.messages.add('Error saving page', 'error')
            console.error(`savePage(id: ${stat.data.id})`, error)
          })
        })
      },


      title(node) {
        const list = []

        if(node.theme) {
          list.push('Theme: ' + node.theme)
        }

        if(node.type) {
          list.push('Page type: ' + node.type)
        }

        if(node.tag) {
          list.push('Tag: ' + node.tag)
        }

        if(node.cache) {
          list.push('Cache: ' + node.cache + ' min')
        }

        return list.join("\n")
      },


      transform(result) {
        const pages = result.data.map(entry => {
          const item = entry.latest?.data ? JSON.parse(entry.latest?.data) : {
            ...entry,
            meta: JSON.parse(entry.meta || '{}'),
            config: JSON.parse(entry.config || '{}'),
          }

          return Object.assign(item, {
            id: entry.id,
            has: entry.has,
            parent_id: entry.parent_id,
            deleted_at: entry.deleted_at,
            updated_at: entry.latest?.created_at || entry.updated_at,
            editor: entry.latest?.editor || entry.editor,
            published: entry.latest?.published ?? true,
          })
        })

        return {
          data: pages,
          currentPage: result.paginatorInfo.currentPage,
          lastPage: result.paginatorInfo.lastPage
        }
      },


      trashed(val) {
        this.trash = val
        this.loading = true

        const promise = this.filter || this.trash === true ? this.search(this.filter) : this.fetch()

        promise.then(result => {
          this.pages = result.data
          this.loading = false
        })
      },


      update(stat, fcn) {
        if(typeof fcn !== 'function') {
          throw new Error('Second paramter must be a function')
        }

        fcn(stat)
        stat.children?.forEach((stat) => {
          fcn(stat, fcn)
        })
      },


      url(node) {
        return this.app.urlpage
          .replace(/:domain/, node.domain || '')
          .replace(/:slug/, node.slug || '')
          .replace(/xx-XX/, node.lang || '')
          .replaceAll('//', '/').replace(':/', '://')
      }
    },

    watch: {
      filter: {
        deep: true,
        handler() {
          this.pages = []
          this.loading = true

          const search = this.debounce(this.search, 500)
          const promise = this.filter ? search(this.filter) : this.fetch()

          promise.then(result => {
            this.pages = result.data
            this.loading = false
          })
        }
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

    <v-app-bar-title>Pages</v-app-bar-title>
  </v-app-bar>

  <Navigation :state="nav" @update:state="$emit('update:nav', $event)" />

  <v-main class="page-tree">
    <v-container>
      <v-sheet class="box">
        <div class="header">
          <v-menu>
            <template #activator="{ props }">
              <v-btn append-icon="mdi-menu-down" variant="outlined" v-bind="props">Actions</v-btn>
            </template>
            <v-list>
              <v-list-item v-show="isChecked && !isTrashed">
                <v-btn prepend-icon="mdi-publish" variant="text" @click="publishAll()">Publish</v-btn>
              </v-list-item>
              <v-list-item v-show="isChecked && !isTrashed">
                <v-btn prepend-icon="mdi-eye" variant="text" @click="status(null, 1)">Enable</v-btn>
              </v-list-item>
              <v-list-item v-show="isChecked && !isTrashed">
                <v-btn prepend-icon="mdi-eye-off" variant="text" @click="status(null, 0)">Disable</v-btn>
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

          <v-menu v-if="Object.keys(languages.available).length">
            <template #activator="{ props }">
              <v-btn append-icon="mdi-menu-down" prepend-icon="mdi-translate" variant="outlined" location="bottom right" v-bind="props">
                {{ languages.available[languages.current] || 'All' }}
              </v-btn>
            </template>
            <v-list>
              <v-list-item>
                <v-btn variant="text" @click="reload()">All</v-btn>
              </v-list-item>
              <v-list-item v-for="(name, code) in languages.available" :key="code">
                <v-btn variant="text" @click="reload(code)">{{ name }}</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>

        <Draggable v-model="pages" ref="tree" :defaultOpen="false" :watermark="false" :statHandler="init" virtualization @change="change()">
          <template #default="{ node, stat }">
            <svg v-if="stat.loading" class="spinner" width="24" height="24" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle class="spin1" cx="4" cy="12" r="3"/><circle class="spin1 spin2" cx="12" cy="12" r="3"/><circle class="spin1 spin3" cx="20" cy="12" r="3"/></svg>
            <v-icon v-else :class="{hidden: !node.has}" size="large" @click="load(stat, node)" :icon="stat.open ? 'mdi-menu-down' : 'mdi-menu-right'"></v-icon>

            <v-checkbox-btn v-model="stat.check" :class="{draft: !node.published}"></v-checkbox-btn>

            <v-menu v-if="node.id">
              <template #activator="{ props }">
                <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
              </template>
              <v-list>
                <v-list-item v-show="!node.deleted_at && !node.published">
                  <v-btn prepend-icon="mdi-publish" variant="text" @click="publish(stat)">Publish</v-btn>
                </v-list-item>
                <v-list-item v-if="node.status !== 0">
                  <v-btn prepend-icon="mdi-eye-off" variant="text" @click="status(stat, 0)">Disable</v-btn>
                </v-list-item>
                <v-list-item v-if="node.status !== 1">
                  <v-btn prepend-icon="mdi-eye" variant="text" @click="status(stat, 1)">Enable</v-btn>
                </v-list-item>
                <v-list-item v-if="node.status !== 2">
                  <v-btn prepend-icon="mdi-eye-off-outline" variant="text" @click="status(stat, 2)">Hide in menu</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn prepend-icon="mdi-content-copy" variant="text" @click="copy(stat, node)">Copy</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn prepend-icon="mdi-content-cut" variant="text" @click="cut(stat, node)">Cut</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('insert')">Insert</v-btn>
                </v-list-item>
                <v-fade-transition v-show="menu.insert">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat, 0)">🠕 Before</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-fade-transition v-show="menu.insert">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat)">🠖 Into</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-fade-transition v-show="menu.insert">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat, 1)">🠗 After</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-list-item v-show="clip && clip.type == 'copy'">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('paste')">Paste</v-btn>
                </v-list-item>
                <v-fade-transition v-show="clip && clip.type == 'copy' && menu.paste">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat, 0)">🠕 Before</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-fade-transition v-show="clip && clip.type == 'copy' && menu.paste">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat)">🠖 Into</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-fade-transition v-show="clip && clip.type == 'copy' && menu.paste">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat, 1)">🠗 After</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-list-item v-show="clip && clip.type == 'cut'">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('move')">Paste</v-btn>
                </v-list-item>
                <v-fade-transition v-show="clip && clip.type == 'cut' && menu.move">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat, 0)">🠕 Before</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-fade-transition v-show="clip && clip.type == 'cut' && menu.move">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat)">🠖 Into</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-fade-transition v-show="clip && clip.type == 'cut' && menu.move">
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat, 1)">🠗 After</v-btn>
                  </v-list-item>
                </v-fade-transition>
                <v-list-item v-if="!node.deleted_at">
                  <v-btn prepend-icon="mdi-delete" variant="text" @click="drop(stat)">Trash</v-btn>
                </v-list-item>
                <v-list-item v-if="node.deleted_at">
                  <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep(stat)">Restore</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge(stat)">Purge</v-btn>
                </v-list-item>
              </v-list>
            </v-menu>
            <div class="node-content"
              :class="{
                'status-hidden': node.status == 2,
                'status-enabled': node.status == 1,
                'status-disabled': !node.status,
                'trashed': node.deleted_at
              }"
              :title="title(node)"
              @click="$emit('update:item', node)">
              <div class="node-text">
                <span class="page-lang" v-if="node.lang">{{ node.lang }}</span>
                <span class="page-name">{{ node.name || 'New' }}</span>
                <div v-if="node.title" class="page-title">{{ node.title }}</div>
              </div>
              <div class="node-url">
                <div class="page-domain">{{ node.domain }}</div>
                <span class="page-slug">{{ url(node) }}</span>
                <span v-if="node.to" class="page-to"> ➔ {{ node.to }}</span>
              </div>
            </div>
            <v-btn icon="mdi-arrow-right" variant="text" @click="$emit('update:item', node)"></v-btn>
          </template>
        </Draggable>
        <p v-if="loading" class="loading">
          Loading
          <svg class="spinner" width="32" height="32" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle class="spin1" cx="4" cy="12" r="3"/><circle class="spin1 spin2" cx="12" cy="12" r="3"/><circle class="spin1 spin3" cx="20" cy="12" r="3"/></svg>
        </p>
        <p v-if="!loading && filter && !pages.length" class="notfound">
          No pages found
        </p>
        <v-btn v-if="!loading && !filter && !pages.length" color="primary" icon="mdi-folder-plus" @click="add()"></v-btn>
      </v-sheet>
    </v-container>
  </v-main>
</template>

<style>
  .page-tree .box {
    margin: 1rem 0;
  }

  .header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 1rem;
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

  .draft {
    background-color: #ffe0c0;
    border-radius: 50%;
  }

  .drag-placeholder {
    height: 48px;
  }

  .drag-placeholder-wrapper .tree-node-inner {
    background-color: #fafafa;
  }

  .tree-node-inner {
    display: flex;
    align-items: center;
    border-bottom: 1px solid rgb(var(--v-theme-primary));
    padding: 0.5rem 0;
  }

  .tree-node-inner .spinner {
    margin-inline-end: 5px;
    width: 28px;
  }

  .node-content,
  .node-url {
    cursor: pointer;
  }

  .node-content {
    justify-content: space-between;
    margin: 0 1%;
    width: 100%;
  }

  .node-text, .node-url {
    text-overflow: ellipsis;
    overflow: hidden;
  }

  .node-url, .page-title {
    font-size: 80%;
    color: #808080;
  }

  .node-url {
    text-align: end;
    align-self: end;
  }

  .page-lang {
    display: inline-block;
    margin-inline-end: 0.5rem;
    text-transform: uppercase;
    vertical-align: middle;
    border-radius: 0.625rem;
    background-color: rgb(var(--v-theme-primary));
    color: #FFFFFF;
    font-weight: bold;
    font-size: 60%;
    height: 1.25rem;
    padding: 0.2rem;
  }

  .page-name {
    font-size: 110%;
  }

  .page-domain {
    color: initial;
  }

  .page-title, .page-domain {
    display: block;
  }

  .page-domain, .page-to {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
  }

  .status-disabled .page-name {
    text-decoration: line-through;
  }

  .status-hidden .node-text {
    color: #808080;
  }

  .page-tree .trashed {
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

  @media (min-width: 600px) {
    .search {
      order: unset;
      width: unset;
      max-width: 30rem;
    }
    .tree-node-inner {
      padding: 0.25rem 0;
    }
    .node-content {
      display: flex;
    }
    .node-text, .node-url {
      max-width: 50%;
    }
  }

  @media (min-width: 960px) {
    .page-title {
      height: 1.25rem;
    }
  }
</style>
