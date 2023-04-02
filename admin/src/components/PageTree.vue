<script>
  import gql from 'graphql-tag'
  import { Draggable } from '@he-tree/vue'
  import { dragContext } from '@he-tree/vue'
  import { useLanguageStore } from '../stores'
  import Navigation from './Navigation.vue'

  export default {
    setup() {
      const languages = useLanguageStore()
      return { languages }
    },
    apollo: {
      pages: {
        query: gql`query($parent: ID, $limit: Int!, $page: Int!) {
          pages(parent_id: $parent, first: $limit, page: $page) {
            data {
              id
              parent_id
              lang
              slug
              name
              title
              to
              tag
              status
              cache
              editor
              created_at
              updated_at
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
            parent: null,
            limit: 50,
            page: 1
          }
        },
        update(result) {
          return (result.pages.data || []).map(node => {
            return {...node}
          });
        }
      }
    },
    components: {
      Draggable,
      Navigation
    },
    props: ['nav', 'id'],
    emits: ['update:nav', 'update:id'],
    data() {
      return {
        me: null,
        clip: null,
        menu: {},
        pages: []
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
        });
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

            if(srcparent && !srcparent.children.length) {
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
        });
      },

      copy(stat, node) {
        this.clip = {type: 'copy', node: node, stat: stat}
      },

      create(attr = {}) {
        return Object.assign({
          slug: '_' + Math.floor(Math.random() * 10000),
          lang: this.languages.current,
          domain: '',
          name: '',
          title: '',
          to: '',
          tag: '',
          config: '{}',
          status: 0,
          cache: 5
        }, attr)
      },

      cut(stat, node) {
        this.clip = {type: 'cut', node: node, stat: stat}
      },

      insert(stat, idx = null) {
        const siblings = this.$refs.tree.getSiblings(stat)
        const parent = idx !== null ? stat.parent : stat
        const pos = siblings.indexOf(stat)
        const node = this.create()
        let refid = null

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
            this.$refs.tree.add(node, parent, idx !== null ? pos + idx : 0)
            parent.data.has = true
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
        });
      },

      load(stat, node) {
        if(!stat.open && !node.children) {
          this.$apollo.query({
            query: gql`query($parent: ID, $limit: Int!, $page: Int!) {
              pages(parent_id: $parent, first: $limit, page: $page) {
                data {
                  id
                  parent_id
                  lang
                  slug
                  name
                  title
                  to
                  tag
                  status
                  cache
                  editor
                  created_at
                  updated_at
                  has
                }
                paginatorInfo {
                  currentPage
                  lastPage
                }
              }
            }`,
            variables: {
              parent: node.id,
              page: stat.page ? stat.page + 1 : 1,
              limit: 50
            }
          }).then(result => {
            if(!result.errors && result.data) {
              const children = (result.data.pages.data || []).map(node => {
                return {...node}
              });
              this.$refs.tree.addMulti(children, stat, 0)
              stat.page = result.data.pages.paginatorInfo.currentPage || 1
            } else {
              console.log(result)
            }
          }).catch(error => {
            console.log(error)
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

            if(!this.clip.stat.children.length) {
              stat.parent.data.has = false
            }
            parent.data.has = true
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
        });

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
        });

        this.show()
      },

      purge() {
        this.$refs.tree.statsFlat.reverse().forEach(stat => {
          if(!stat.check) {
            return
          }

          if(!stat.data.id) {
            this.$refs.tree.remove(stat)

            if(!stat.parent.children.length) {
              stat.parent.data.has = false
            }
          }

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
            if(!result.errrors) {
              this.$refs.tree.remove(stat)

              if(!stat.parent.children.length) {
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

      remove(stat) {
        if(!stat.data.id) {
          this.$refs.tree.remove(stat)

          if(!stat.parent.children.length) {
            stat.parent.data.has = false
          }

          return
        }

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
            this.$refs.tree.remove(stat)

            if(!stat.parent.children.length) {
              stat.parent.data.has = false
            }
          } else {
            console.log(result)
          }
        }).catch(error => {
          console.log(error)
        });
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
        });

        list.forEach((stat, idx) => {
          this.$apollo.mutate({
            mutation: gql`mutation ($id: ID!, $input: PageInput!) {
              savePage(id: $id, input: $input) {
                id
              }
            }`,
            variables: {
              id: stat.data.id,
              input: {
                status: val,
                cache: stat.data.cache || 5,
                config: stat.data.config || '{}'
              }
            }
          }).then(result => {
            if(!result.errors) {
              list[idx].data.status = val
            } else {
              console.log(result)
            }
          }).catch(error => {
            console.log(error)
          })
        })
      }
    },
  }
</script>

<template>
  <v-app-bar :elevation="2" density="compact" image="src/assets/background.png">
    <template v-slot:prepend>
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
            <template v-slot:activator="{ props }">
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
              <v-list-item>
                <v-btn prepend-icon="mdi-delete" variant="text" @click="purge()">Delete</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>

          <v-text-field prepend-inner-icon="mdi-magnify" label="Search" variant="underlined" class="search"
            clearable hide-details>
          </v-text-field>

          <v-menu v-if="Object.keys(languages.available).length">
            <template v-slot:activator="{ props }">
              <v-btn append-icon="mdi-menu-down" variant="outlined" location="bottom right" v-bind="props">
                {{ languages.available[languages.current] || '' }}
              </v-btn>
            </template>
            <v-list>
              <v-list-item v-for="(name, code) in languages.available" :key="code">
                <v-btn variant="text">{{ name }}</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>

        <Draggable v-model="pages" ref="tree" :defaultOpen="false" :watermark="false" virtualization @change="change()">
          <template #default="{ node, stat }">
            <v-icon :class="{hidden: !node.has}" :icon="`mdi-menu-${stat.open ? 'down' : 'right'}`" size="large" @click="load(stat, node)"></v-icon>
            <v-checkbox-btn v-model="stat.check"></v-checkbox-btn>
            <v-menu v-if="node.id">
              <template v-slot:activator="{ props }">
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
                <v-list-item v-if="menu.insert">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat, 0)">➔ Before</v-btn>
                </v-list-item>
                <v-list-item v-if="menu.insert">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat)">➔ Into</v-btn>
                </v-list-item>
                <v-list-item v-if="menu.insert">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(stat, 1)">➔ After</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'copy'">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('paste')">Paste</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'copy' && menu.paste">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat, 0)">➔ Before</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'copy' && menu.paste">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat)">➔ Into</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'copy' && menu.paste">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(stat, 1)">➔ After</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'cut'">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click.stop="show('move')">Paste</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'cut' && menu.move">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat, 0)">➔ Before</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'cut' && menu.move">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat)">➔ Into</v-btn>
                </v-list-item>
                <v-list-item v-if="clip && clip.type == 'cut' && menu.move">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="move(stat, 1)">➔ After</v-btn>
                </v-list-item>
                <v-list-item>
                  <v-btn prepend-icon="mdi-delete" variant="text" @click="remove(stat)">Delete</v-btn>
                </v-list-item>
              </v-list>
            </v-menu>
            <div class="node-content" :class="{'status-hidden': node.status == 2, 'status-enabled': node.status == 1, 'status-disabled': !node.status}" @click="$emit('update:id', '1')">
              <div class="node-text">
                <div class="page-name">{{ node.name || 'New' }}</div>
                <div v-if="node.title" class="page-title">{{ node.title }}</div>
              </div>
              <div class="node-url" @click="$emit('update:id', '1')">
                <div class="page-domain">{{ node.domain }}</div>
                <span class="page-slug">/{{ node.slug }}</span>
                <span v-if="node.to" class="page-to"> ➔ {{ node.to }}</span>
              </div>
            </div>
            <v-btn icon="mdi-arrow-right" variant="text" @click="$emit('update:id', '1')"></v-btn>
          </template>
        </Draggable>
        <v-btn v-if="!pages.length" color="primary" icon="mdi-folder-plus" @click="add()"></v-btn>
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

  .status-disabled .node-text {
    text-decoration: line-through;
  }

  .status-hidden .node-text {
    color: #808080;
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
