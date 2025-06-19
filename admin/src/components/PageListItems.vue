<script>
  import gql from 'graphql-tag'
  import { Draggable } from '@he-tree/vue'
  import { dragContext } from '@he-tree/vue'
  import { useAppStore, useAuthStore, useLanguageStore, useMessageStore } from '../stores'

  export default {
    components: {
      Draggable
    },

    props: {
      'embed': {type: Boolean, default: false},
      'filter': {type: Object, default: () => ({})},
    },

    emits: ['select'],

    data() {
      return {
        clip: null,
        menu: {},
        items: [],
        loading: true,
        checked: false,
        term: '',
      }
    },

    setup() {
      const languages = useLanguageStore()
      const messages = useMessageStore()
      const auth = useAuthStore()
      const app = useAppStore()

      return { app, auth, languages, messages }
    },

    created() {
      this.searchd = this.debounce(this.search, 500)

      this.fetch().then(result => {
        this.items = result.data
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
        if(this.embed || !this.auth.can('page:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const item = this.create()

        this.$apollo.mutate({
          mutation: gql`mutation ($input: PageInput!) {
            addPage(input: $input) {
              id
            }
          }`,
          variables: {
            input: item
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          item.id = result.data.addPage.id
          item.published = true

          this.$refs.tree.add(item)
          this.$emit('select', item)
        }).catch(error => {
          this.messages.add('Error adding root page', 'error')
          this.$log(`PageList::add(): Error adding root page`, error)
        })
      },


      change() {
        if(!this.auth.can('page:move')) {
          this.messages.add('Permission denied', 'error')
          return
        }

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

          if(srcparent?.data && !srcparent?.children.length) {
            srcparent.data.has = false
          }

          if(parent) {
            parent.data.has = true
          }
        }).catch(error => {
          this.messages.add('Error moving page', 'error')
          this.$log(`PageList::change(): Error moving page`, error)
        })
      },


      copy(stat, node) {
        this.clip = {type: 'copy', node: node, stat: stat}
      },


      create(attr = {}) {
        return Object.assign({
          path: '_' + Math.floor(Math.random() * 10000),
          lang: this.languages.current || this.languages.default(),
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
        if(!this.auth.can('page:drop')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const list = (stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data?.id
        }))

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: [ID!]!) {
            dropPage(id: $id) {
              id
            }
          }`,
          variables: {
            id: list.map(item => item.data.id)
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          for(const item of list) {
            this.update(item, (item) => {
              item.data.deleted_at = (new Date).toISOString().replace(/T/, ' ').substring(0, 19)
              item.check = false
            })
          }

          this.invalidate()
        }).catch(error => {
          this.messages.add('Error trashing page', 'error')
          this.$log(`PageList::drop(): Error trashing page`, list, error)
        })
      },


      fetch(parent = null, page = 1, limit = 100) {
        if(!this.auth.can('page:view')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve([])
        }

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
            },
            page: page,
            limit: limit,
            trashed: this.filter.trashed || 'WITHOUT'
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          return this.transform(result.data.pages)
        }).catch(error => {
          this.messages.add('Error fetching pages', 'error')
          this.$log(`PageList::fetch(): Error fetching page`, parent, page, limit, error)
        })
      },


      fields() {
        return `id
          parent_id
          lang
          path
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
            id
            published
            publish_at
            data
            editor
            created_at
          }`
      },


      insert(stat, idx = null) {
        if(!this.auth.can('page:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

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

          if(parent) {
            parent.data.has = true
          }

          this.invalidate()
        }).catch(error => {
          this.messages.add('Error inserting page', 'error')
          this.$log(`PageList::insert(): Error inserting page`, error)
        })
      },


      invalidate() {
        const cache = this.$apollo.provider.defaultClient.cache
        cache.evict({id: 'ROOT_QUERY', fieldName: 'pages'})
        cache.gc()
      },


      keep(stat) {
        if(!this.auth.can('page:keep')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const stats = stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id && stat.data.deleted_at
        })
        const list = stats.filter(stat => {
          return stats.indexOf(stat.parent) === -1
        })
        const deleted_at = stat.data.deleted_at || null

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: [ID!]!) {
            keepPage(id: $id) {
              id
            }
          }`,
          variables: {
            id: list.map(item => item.data.id)
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          for(const item of list) {
            this.update(item, (item) => {
              if(deleted_at >= item.data.deleted_at) {
                item.data.deleted_at = null
                item.check = false
              }
            })
          }

          this.invalidate()
        }).catch(error => {
          this.messages.add('Error restoring page', 'error')
          this.$log(`PageList::keep(): Error restoring page`, list, error)
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
        if(!this.auth.can('page:move')) {
          this.messages.add('Permission denied', 'error')
          return
        }

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

          if(parent) {
            if(!this.clip.stat.children?.length) {
              stat.parent.data.has = false
            }
            parent.data.has = true
          }

          this.invalidate()
        }).catch(error => {
          this.messages.add('Error moving page', 'error')
          this.$log(`PageList::move(): Error moving page`, stat, idx, error)
        })

        this.show()
      },


      paste(stat, idx = null) {
        if(!this.auth.can('page:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const siblings = this.$refs.tree.getSiblings(stat)
        const parent = idx !== null ? stat.parent : stat
        const pos = siblings.indexOf(stat)
        const node = {...this.clip.node}
        let refid = null

        node.path = node.path + '_' + Math.floor(Math.random() * 10000)
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

          if(parent) {
            parent.data.has = true
          }

          this.invalidate()
        }).catch(error => {
          this.messages.add('Error copying page', 'error')
          this.$log(`PageList::paste(): Error copying page`, stat, idx, error)
        })

        this.show()
      },


      publish(stat) {
        if(!this.auth.can('page:publish')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const list = stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id && !stat.data.published
        })

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: [ID!]!) {
            pubPage(id: $id) {
              id
            }
          }`,
          variables: {
            id: list.map(item => item.data.id)
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          for(const item of list) {
            item.data.published = true
            item.check = false
          }

          this.invalidate()
        }).catch(error => {
          this.messages.add('Error publishing page', 'error')
          this.$log(`PageList::publish(): Error publishing page`, list, error)
        })
      },


      purge(stat) {
        if(!this.auth.can('page:purge')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const list = stat ? [stat] : this.$refs.tree.statsFlat.filter(stat => {
          return stat.check && stat.data.id
        })

        if(!list.length) {
          return
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: [ID!]!) {
            purgePage(id: $id) {
              id
            }
          }`,
          variables: {
            id: list.map(item => item.data.id).reverse()
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          for(const item of list) {
            this.$refs.tree.remove(item)

            if(item.parent && !item.parent.children?.length) {
              item.parent.data.has = false
            }
          }
        }).catch(error => {
          this.messages.add('Error purging page', 'error')
          this.$log(`PageList::purge(): Error purging page`, list, error)
        })
      },


      search(page = 1, limit = 100) {
        if(!this.auth.can('page:view')) {
          this.messages.add('Permission denied', 'error')
          return Promise.resolve([])
        }

        const publish = this.filter.publish || null
        const trashed = this.filter.trashed || 'WITHOUT'
        const filter = {...this.filter}

        delete filter.trashed
        delete filter.publish

        if(this.term) {
          filter.any = this.term
        }

        return this.$apollo.query({
          query: gql`query($filter: PageFilter, $limit: Int!, $page: Int!, $trashed: Trashed, $publish: Publish) {
            pages(filter: $filter, first: $limit, page: $page, trashed: $trashed, publish: $publish) {
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
            filter: filter,
            page: page,
            limit: limit,
            trashed: trashed,
            publish: publish
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          return this.transform(result.data.pages)
        }).catch(error => {
          this.messages.add('Error searching pages', 'error')
          this.$log(`PageList::search(): Error searching pages`, page, limit, error)
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
        if(!this.auth.can('page:save')) {
          this.messages.add('Permission denied', 'error')
          return
        }

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
            this.$log(`PageList::status(): Error saving page`, stat, val, error)
          })
        })
      },


      title(item) {
        const list = []

        if(item.publish_at) {
          list.push('Publish at: ' + (new Date(item.publish_at)).toLocaleDateString())
        }

        if(item.theme) {
          list.push('Theme: ' + item.theme)
        }

        if(item.type) {
          list.push('Page type: ' + item.type)
        }

        if(item.tag) {
          list.push('Tag: ' + item.tag)
        }

        if(item.cache) {
          list.push('Cache: ' + item.cache + ' min')
        }

        return list.join("\n")
      },


      toggle() {
        this.$refs.tree.statsFlat.forEach(el => {
          el.check = !el.check
        })
      },


      transform(result) {
        const pages = result.data.map(entry => {
          const item = entry.latest?.data ? JSON.parse(entry.latest?.data || '{}') : {
            ...entry,
            meta: JSON.parse(entry.meta || '{}'),
            config: JSON.parse(entry.config || '{}'),
          }

          return Object.assign(item, {
            id: entry.id,
            has: entry.has,
            parent_id: entry.parent_id,
            deleted_at: entry.deleted_at,
            created_at: entry.created_at,
            updated_at: entry.latest?.created_at || entry.updated_at,
            editor: entry.latest?.editor || entry.editor,
            published: entry.latest?.published ?? true,
            publish_at: entry.latest?.publish_at || null,
            latest: entry.latest,
          })
        })

        return {
          data: pages,
          currentPage: result.paginatorInfo.currentPage,
          lastPage: result.paginatorInfo.lastPage
        }
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
          .replace(/:path/, node.path || '')
          .replace(/\/+$/, '/')
      }
    },

    watch: {
      filter: {
        deep: true,
        handler(filter) {
          this.items = []
          this.loading = true

          const promise = filter.publish ? this.search() : this.fetch()

          promise.then(result => {
            this.items = result.data
            this.loading = false
          })
        }
      },


      term: {
        handler() {
          this.items = []
          this.loading = true

          const promise = this.term ? this.searchd() : this.fetch()

          promise.then(result => {
            this.items = result.data
            this.loading = false
          })
        }
      }
    }
  }
</script>

<template>
  <div class="header">
    <div class="bulk">
      <v-checkbox-btn v-model="checked" @click.stop="toggle()"></v-checkbox-btn>
      <v-menu>
        <template #activator="{ props }">
          <v-btn append-icon="mdi-menu-down" variant="text" v-bind="props">Actions</v-btn>
        </template>
        <v-list>
          <v-list-item v-if="isChecked && auth.can('page:publish')">
            <v-btn prepend-icon="mdi-publish" variant="text" @click="publish()">Publish</v-btn>
          </v-list-item>
          <v-list-item v-if="isChecked && auth.can('page:save')">
            <v-btn prepend-icon="mdi-eye" variant="text" @click="status(null, 1)">Enable</v-btn>
          </v-list-item>
          <v-list-item v-if="isChecked && auth.can('page:save')">
            <v-btn prepend-icon="mdi-eye-off" variant="text" @click="status(null, 0)">Disable</v-btn>
          </v-list-item>
          <v-list-item v-if="canTrash && auth.can('page:drop')">
            <v-btn prepend-icon="mdi-delete" variant="text" @click="drop()">Trash</v-btn>
          </v-list-item>
          <v-list-item v-if="isTrashed && auth.can('page:keep')">
            <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep()">Restore</v-btn>
          </v-list-item>
          <v-list-item v-if="isChecked && auth.can('page:purge')">
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
  </div>

  <Draggable v-model="items" ref="tree"
    :defaultOpen="false"
    :disableDrag="!auth.can('page:move')"
    :watermark="false"
    virtualization
    @change="change()"
  >
    <template #default="{ node, stat }">
      <svg v-if="stat.loading" class="spinner" width="24" height="24" viewBox="0 0 28 28" xmlns="http://www.w3.org/2000/svg"><circle class="spin1" cx="4" cy="12" r="3"/><circle class="spin1 spin2" cx="12" cy="12" r="3"/><circle class="spin1 spin3" cx="20" cy="12" r="3"/></svg>
      <v-icon v-else :class="{hidden: !node.has}" size="large" @click="load(stat, node)" :icon="stat.open ? 'mdi-menu-down' : 'mdi-menu-right'"></v-icon>

      <v-checkbox-btn v-model="stat.check" :class="{draft: !node.published}"></v-checkbox-btn>

      <v-menu v-if="node.id">
        <template #activator="{ props }">
          <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
        </template>
        <v-list>
          <v-list-item v-if="!node.deleted_at && !node.published && auth.can('page:publish')">
            <v-btn prepend-icon="mdi-publish" variant="text" @click="publish(stat)">Publish</v-btn>
          </v-list-item>
          <v-list-item v-if="node.status !== 0 && auth.can('page:save')">
            <v-btn prepend-icon="mdi-eye-off" variant="text" @click="status(stat, 0)">Disable</v-btn>
          </v-list-item>
          <v-list-item v-if="node.status !== 1 && auth.can('page:save')">
            <v-btn prepend-icon="mdi-eye" variant="text" @click="status(stat, 1)">Enable</v-btn>
          </v-list-item>
          <v-list-item v-if="node.status !== 2 && auth.can('page:save')">
            <v-btn prepend-icon="mdi-eye-off-outline" variant="text" @click="status(stat, 2)">Hide in menu</v-btn>
          </v-list-item>
          <v-list-item v-if="auth.can('page:move')">
            <v-btn prepend-icon="mdi-content-cut" variant="text" @click="cut(stat, node)">Cut</v-btn>
          </v-list-item>
          <v-list-item v-if="!this.embed && auth.can('page:add')">
            <v-btn prepend-icon="mdi-content-copy" variant="text" @click="copy(stat, node)">Copy</v-btn>
          </v-list-item>
          <v-list-item v-if="!this.embed && auth.can('page:add')">
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('insert')">Insert</v-btn>
          </v-list-item>
          <v-fade-transition v-if="menu.insert && !this.embed && auth.can('page:add')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat, 0)">🠕 Before</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-fade-transition v-if="menu.insert && !this.embed && auth.can('page:add')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat)">🠖 Into</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-fade-transition v-if="menu.insert && !this.embed && auth.can('page:add')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat, 1)">🠗 After</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-list-item v-if="clip && clip.type == 'copy' && !this.embed && auth.can('page:add')">
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('paste')">Paste</v-btn>
          </v-list-item>
          <v-fade-transition v-if="clip && clip.type == 'copy' && menu.paste && !this.embed && auth.can('page:add')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat, 0)">🠕 Before</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-fade-transition v-if="clip && clip.type == 'copy' && menu.paste && !this.embed && auth.can('page:add')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat)">🠖 Into</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-fade-transition v-if="clip && clip.type == 'copy' && menu.paste && !this.embed && auth.can('page:add')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat, 1)">🠗 After</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-list-item v-if="clip && clip.type == 'cut' && auth.can('page:move')">
            <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('move')">Paste</v-btn>
          </v-list-item>
          <v-fade-transition v-if="clip && clip.type == 'cut' && menu.move && auth.can('page:move')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat, 0)">🠕 Before</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-fade-transition v-if="clip && clip.type == 'cut' && menu.move && auth.can('page:move')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat)">🠖 Into</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-fade-transition v-if="clip && clip.type == 'cut' && menu.move && auth.can('page:move')">
            <v-list-item>
              <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat, 1)">🠗 After</v-btn>
            </v-list-item>
          </v-fade-transition>
          <v-list-item v-if="!node.deleted_at && auth.can('page:drop')">
            <v-btn prepend-icon="mdi-delete" variant="text" @click="drop(stat)">Trash</v-btn>
          </v-list-item>
          <v-list-item v-if="node.deleted_at && auth.can('page:keep')">
            <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep(stat)">Restore</v-btn>
          </v-list-item>
          <v-list-item v-if="auth.can('page:purge')">
            <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge(stat)">Purge</v-btn>
          </v-list-item>
        </v-list>
      </v-menu>
      <div class="item-content"
        :class="{
          'status-hidden': node.status == 2,
          'status-enabled': node.status == 1,
          'status-disabled': !node.status,
          'trashed': node.deleted_at
        }"
        :title="title(node)"
      >
        <div class="item-text" @click="$emit('select', node)">
          <v-icon v-if="node.publish_at" class="publish-at" icon="mdi-clock-outline"></v-icon>
          <span class="item-lang" v-if="node.lang">{{ node.lang }}</span>
          <span class="item-title">{{ node.name || 'New' }}</span>
          <div v-if="node.title" class="item-subtitle">{{ node.title }}</div>
        </div>
        <a class="item-aux" :href="url(node)" target="_blank" draggable="false">
          <div class="item-domain">{{ node.domain }}</div>
          <span class="item-path item-subtitle">{{ url(node) }}</span>
          <span v-if="node.to" class="item-to item-subtitle"> ➔ {{ node.to }}</span>
        </a>
      </div>
    </template>
  </Draggable>

  <p v-if="loading" class="loading">
    Loading
    <svg class="spinner" width="32" height="32" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><circle class="spin1" cx="4" cy="12" r="3"/><circle class="spin1 spin2" cx="12" cy="12" r="3"/><circle class="spin1 spin3" cx="20" cy="12" r="3"/></svg>
  </p>

  <p v-if="!loading && !items.length" class="notfound">
    No items found
  </p>

  <div v-if="!loading && !items.length && !this.embed && this.auth.can('page:add')" class="btn-group">
    <v-btn @click="add()"
      icon="mdi-folder-plus"
      color="primary"
      elevation="0"
    ></v-btn>
  </div>
</template>

<style>
  .drag-placeholder {
    height: 48px;
  }

  .drag-placeholder-wrapper .tree-node-inner {
    background-color: #fafafa;
  }

  .tree-node-inner {
    border-bottom: 1px solid rgba(var(--v-border-color), 0.38);
    align-items: center;
    display: flex;
    padding: 4px 0;
    user-select: none;
  }

  .tree-node-inner .spinner {
    transform: rotate(90deg);
    width: 28px;
  }

  .item-domain {
    color: initial;
    display: block;
  }

  .item-domain,
  .item-to {
    white-space: nowrap;
    text-overflow: ellipsis;
    overflow: hidden;
  }

  .status-disabled .item-title {
    text-decoration: line-through;
  }

  .status-hidden .item-text {
    color: #808080;
  }

  @media (min-width: 600px) {
    .tree-node-inner {
      padding: 4px 0;
    }
  }
</style>
