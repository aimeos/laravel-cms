<script>
  import gql from 'graphql-tag'
  import { Draggable } from '@he-tree/vue'
  import { dragContext } from '@he-tree/vue'
  import { useLanguageStore } from '../stores'
  import { useAppStore } from '../stores'
  import Navigation from './Navigation.vue'

  export default {
    setup() {
      const languages = useLanguageStore()
      const app = useAppStore()
      return { app, languages }
    },
    apollo: {
      pages: {
        query: gql`query($parent: ID, $lang: String!, $limit: Int!, $page: Int!) {
          pages(parent_id: $parent, lang: $lang, first: $limit, page: $page) {
            data {
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
              start
              end
              editor
              created_at
              updated_at
              deleted_at
              has
            }
            paginatorInfo {
              currentPage
              lastPage
            }
          }
        }`,
        variables() {
          return {
            lang: this.languages.current,
            parent: null,
            limit: 50,
            page: 1
          }
        },
        update(result) {
          return (result.pages.data || []).map(node => {
            return {...node}
          })
        }
      }
    },
    components: {
      Draggable,
      Navigation
    },
    props: ['nav', 'item'],
    emits: ['update:nav', 'update:item'],
    data() {
      return {
        clip: null,
        menu: {},
        pages: [],
        trash: false
      }
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
          if(!result.errors && result.data && result.data.addPage.id) {
            node.id = result.data.addPage.id
            this.$refs.tree.add(node)
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
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
          if(!result.errors && result.data && result.data.movePage.id) {
            const srcparent = dragContext.startInfo.parent

            if(!srcparent?.children.length) {
              srcparent.data.has = false
            }

            if(parent) {
              parent.data.has = true
            }
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
        })
      },

      copy(stat, node) {
        this.clip = {type: 'copy', node: node, stat: stat}
      },

      create(attr = {}) {
        return Object.assign({
          slug: '_' + Math.floor(Math.random() * 10000),
          lang: this.languages.current,
          config: '{}',
          status: 0,
          cache: 5
        }, attr)
      },

      cut(stat, node) {
        this.clip = {type: 'cut', node: node, stat: stat}
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
            if(!result.errors && result.data && result.data.dropPage.id) {
              this.update(stat, (stat) => {
                stat.data.deleted_at = (new Date).toISOString().replace(/T/, ' ').substring(0, 19)
                stat.hidden = !this.trash
                stat.check = false
              })

              if(stat.parent && !stat.parent.children?.length) {
                stat.parent.data.has = false
              }
            } else {
              console.log(result)
            }
          }).catch(error => {
            console.log(error)
          })
        })
      },

      init(stat) {
        if(stat.data.deleted_at && !this.trash) {
          stat.hidden = true
        }

        return stat
      },

      insert(stat, idx = null) {
        const siblings = this.$refs.tree.getSiblings(stat)
        const parent = idx !== null ? stat.parent : stat
        const node = this.create({domain: parent?.data.domain})
        const pos = siblings.indexOf(stat)
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
          if(!result.errors && result.data && result.data.addPage.id) {
            node.id = result.data.addPage.id
            if(idx !== null || stat.open) {
              this.$refs.tree.add(node, parent, idx !== null ? pos + idx : 0)
            }
            parent.data.has = true
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
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
            if(!result.errors) {
              const deleted_at = stat.data.deleted_at

              this.update(stat, (stat) => {
                if(deleted_at >= stat.data.deleted_at) {
                  stat.data.deleted_at = null
                  stat.hidden = !this.trash
                  stat.check = false
                }
              })
            } else {
              console.log(result)
            }
          }).catch(error => {
            console.log(error)
          })
        })
      },

      load(stat, node) {
        if(!stat.open && !node.children) {
          stat.loading = true

          this.$apollo.query({
            query: gql`query($parent: ID, $limit: Int!, $page: Int!) {
              pages(parent_id: $parent, first: $limit, page: $page) {
                data {
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
                  start
                  end
                  editor
                  created_at
                  updated_at
                  deleted_at
                  has
                }
                paginatorInfo {
                  currentPage
                  lastPage
                }
              }
            }`,
            variables: { // no lang restriction when fetching children!
              parent: node.id,
              page: stat.page ? stat.page + 1 : 1,
              limit: 50
            }
          }).then(result => {
            if(!result.errors && result.data) {
              const children = (result.data.pages.data || []).map(node => {
                return {...node}
              })
              this.$refs.tree.addMulti(children, stat, 0)
              stat.page = result.data.pages.paginatorInfo.currentPage || 1
            } else {
              console.log(result)
            }
          }).catch(error => {
            console.log(error)
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
          if(!result.errors && result.data && result.data.movePage.id) {
            const index = idx !== null ? (pos < this.$refs.tree.getSiblings(stat).indexOf(this.clip.stat) ? pos + idx : pos) : 0

            this.$refs.tree.move(this.clip.stat, parent, index)

            if(!this.clip.stat.children?.length) {
              stat.parent.data.has = false
            }
            parent.data.has = true
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
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
        node.children = null
        node.status = 0
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
          if(!result.errors && result.data && result.data.addPage.id) {
            const index = idx !== null ? this.$refs.tree.getSiblings(stat).indexOf(stat) + idx : 0

            this.$refs.tree.add(node, parent, index)
            parent.data.has = true
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
        })

        this.show()
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
            if(!result.errrors) {
              this.$refs.tree.remove(stat)

              if(stat.parent && !stat.parent.children?.length) {
                stat.parent.data.has = false
              }
            } else {
              console.log(result)
            }
          }).catch(error => {
            console.log(error)
          })
        })
      },

      reload(lang) {
        this.loading = true
        this.languages.current = lang

        this.$apollo.query({
          query: gql`query($parent: ID, $lang: String!, $limit: Int!, $page: Int!) {
            pages(parent_id: $parent, lang: $lang, first: $limit, page: $page) {
              data {
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
                start
                end
                editor
                created_at
                updated_at
                deleted_at
                has
              }
              paginatorInfo {
                currentPage
                lastPage
              }
            }
          }`,
          variables: {
            parent: null,
            lang: lang,
            page: 1,
            limit: 50
          }
        }).then(result => {
          if(!result.errors && result.data) {
            this.pages = (result.data.pages.data || []).map(node => {
              return {...node}
            })
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
        }).finally(() => {
          this.loading = false
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
            if(!result.errors) {
              stat.data.status = val
            } else {
              console.log(result)
            }
          }).catch(error => {
            console.log(error)
          })
        })
      },

      trashed(val) {
        this.trash = val

        this.$refs.tree.statsFlat.forEach(stat => {
          stat.hidden = stat.data.deleted_at && !val
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
        const url = this.app.url.replace(/:slug/, node.slug).replace(/:lang/, node.lang)
        const end = url.endsWith('/') ? url.length - 1 : url.length
        const start = url.startsWith('//') ? 1 : 0

        return url.substring(start, end)
      }
    },
  }
</script>

<template>
  <v-app-bar :elevation="2" density="compact" image="src/assets/background.png">
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

  <v-main>
    <v-container>
      <v-sheet>
        <div class="header">
          <v-menu>
            <template #activator="{ props }">
              <v-btn append-icon="mdi-menu-down" variant="outlined" v-bind="props">Actions</v-btn>
            </template>
            <v-list>
              <v-list-item>
                <v-btn prepend-icon="mdi-eye-off" variant="text" @click="status(null, 0)">Disable</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn prepend-icon="mdi-eye" variant="text" @click="status(null, 1)">Enable</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn prepend-icon="mdi-eye-off-outline" variant="text" @click="status(null, 2)">Hide in menu</v-btn>
              </v-list-item>
              <v-list-item v-show="!trash">
                <v-btn prepend-icon="mdi-delete-circle-outline" variant="text" @click="trashed(true)">Show trashed</v-btn>
              </v-list-item>
              <v-list-item v-show="trash">
                <v-btn prepend-icon="mdi-delete-off" variant="text" @click="trashed(false)">Hide trashed</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn prepend-icon="mdi-delete" variant="text" @click="drop()">Trash</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn prepend-icon="mdi-delete-restore" variant="text" @click="keep()">Restore</v-btn>
              </v-list-item>
              <v-list-item>
                <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge()">Delete</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>

          <!-- v-text-field prepend-inner-icon="mdi-magnify" label="Search" variant="underlined" class="search"
            clearable hide-details>
          </v-text-field -->

          <v-menu v-if="Object.keys(languages.available).length">
            <template #activator="{ props }">
              <v-btn append-icon="mdi-menu-down" variant="outlined" location="bottom right" v-bind="props">
                {{ languages.available[languages.current] || 'None' }}
              </v-btn>
            </template>
            <v-list>
              <v-list-item>
                <v-btn variant="text" @click="reload('')">None</v-btn>
              </v-list-item>
              <v-list-item v-for="(name, code) in languages.available" :key="code">
                <v-btn variant="text" @click="reload(code)">{{ name }}</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>

        <Draggable v-model="pages" ref="tree" :defaultOpen="false" :watermark="false" :statHandler="init" virtualization @change="change()">
          <template #default="{ node, stat }">
            <v-icon :class="{hidden: !node.has, load: stat.loading}" size="large" @click="load(stat, node)"
              :icon="stat.loading ? 'mdi-loading' : (stat.open ? 'mdi-menu-down' : 'mdi-menu-right')">
            </v-icon>
            <v-checkbox-btn v-model="stat.check"></v-checkbox-btn>
            <v-menu v-if="node.id">
              <template #activator="{ props }">
                <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
              </template>
              <v-list>
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
                  <v-btn prepend-icon="mdi-delete-forever" variant="text" @click="purge(stat)">Delete</v-btn>
                </v-list-item>
              </v-list>
            </v-menu>
            <div class="node-content"
              :class="{'status-hidden': node.status == 2, 'status-enabled': node.status == 1, 'status-disabled': !node.status, 'trashed': node.deleted_at}"
              @click="$emit('update:item', node)">
              <div class="node-text">
                <div class="page-name">
                  <v-icon class="page-time" size="x-small" v-if="node.start || node.end">mdi-clock-outline</v-icon>
                  {{ node.name || 'New' }}
                </div>
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
        <p v-if="$apollo.loading" class="loading">Loading ...</p>
        <v-btn v-if="!$apollo.loading && !pages.length" color="primary" icon="mdi-folder-plus" @click="add()"></v-btn>
      </v-sheet>
    </v-container>
  </v-main>
</template>

<style>
  .header {
    display: flex;
    flex-wrap: wrap;
    align-items: flex-end;
    justify-content: space-between;
    margin-bottom: 1rem;
  }

  .v-input.search {
    width: 100%;
    order: 3;
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
    border-bottom: 1px solid #103050;
    padding: 0.5rem 0;
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

  .page-time {
    vertical-align: text-top;
  }

  .page-domain {
    color: initial;
  }

  .page-name, .page-title, .page-domain {
    display: block;
  }

  .page-title {
    height: 2.5rem;
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

  .trashed {
    text-decoration: line-through;
  }

  .loading {
    animation: blink 1s;
    animation-direction: alternate;
    animation-iteration-count: infinite;
  }

  @keyframes blink {
    0% {
      opacity: 0;
    }

    100% {
      opacity: 1;
    }
  }

  .load {
    animation: rotate 2s;
    animation-iteration-count: infinite;
  }

  @keyframes rotate {
    100% {
      transform: rotate(360deg);
    }
  }

  @media (min-width: 500px) {
    .v-input.search {
      max-width: 30rem;
      margin: 0 1rem;
      width: unset;
      order: unset;
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
