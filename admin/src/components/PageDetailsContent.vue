<script>
  import gql from 'graphql-tag'
  import Fields from './Fields.vue'
  import History from './History.vue'
  import Elements from './Elements.vue'
  import { VueDraggable } from 'vue-draggable-plus'
  import { useElementStore, useSideStore } from '../stores'

  export default {
    components: {
      Fields,
      History,
      Elements,
      VueDraggable
    },

    props: {
      'item': {type: Object, required: true},
      'contents': {type: Array, required: true}
    },

    emits: ['update:contents'],

    data: () => ({
      list: [],
      panel: [],
      menu: {},
      side: {},
      clip: null,
      index: null,
      checked: false,
      vhistory: false,
      velements: false,
      currentPage: 1,
      lastPage: 1,
    }),

    setup() {
      const elements = useElementStore()
      const aside = useSideStore()
      return { aside, elements }
    },

    created() {
      this.$apollo.query({
        query: gql`query($id: [ID!]) {
          contents(id: $id) {
            data {
              id
              type
              lang
              editor
              updated_at
              versions {
                published
                data
                refs
                editor
                created_at
                files {
                  id
                  mime
                  path
                  previews
                  editor
                  updated_at
                }
              }
            }
          }
        }`,
        variables: {
          id: this.contents
        }
      }).then(result => {
        if(result.errors) {
          throw result.errors
        }

        this.lastPage = result.data.contents?.paginatorInfo?.lastPage || 1
        this.currentPage = result.data.contents?.paginatorInfo?.currentPage || 1
        this.list = result.data.contents?.data?.map(el => {
          const latest = el.versions?.at(-1)
          return {
            id: el.id,
            type: el.type,
            lang: el.lang,
            data: JSON.parse(latest?.data || '{}'),
            files: latest?.files || []
          }
        })
      }).catch(error => {
        console.error(`contents()`, error)
      })
    },

    computed: {
      changed() {
        return this.list.some(el => el._changed)
      }
    },

    methods: {
      add(type, idx) {
        const entry = {type: type, data: {}, files: []}

        if(idx !== null) {
          this.list.splice(idx, 0, entry)
          this.panel.push(this.panel.includes(idx) ? idx + 1 : idx)
        } else {
          this.list.push(entry)
          this.panel.push(this.list.length - 1)
        }

        this.velements = false
      },


      clean() {
        return this.list.map(c => {
          delete c._checked
          delete c._hide
          return c
        })
      },


      copy(idx) {
        const entry = JSON.parse(JSON.stringify(this.list[idx]))
        entry['id'] = null

        this.clip = {type: 'copy', index: idx, content: entry}
      },


      cut(idx) {
        this.clip = {type: 'cut', index: idx, content: this.list[idx]}
        this.list.splice(idx, 1)
      },


      fields(type) {
        if(!this.elements.content[type]?.fields) {
          console.warn(`No definition of fields for "${type}" available`)
          return []
        }

        return this.elements.content[type]?.fields
      },


      insert(idx) {
        this.index = idx
        this.velements = true
      },


      paste(idx) {
        this.list.splice(idx, 0, this.clip.content)
        this.clip = null
      },


      purge() {
        for(let i = this.list.length - 1; i >= 0; i--) {
          this.list[i]._checked ? this.remove(i) : null
        }
        this.checked = false
      },


      remove(idx) {
        this.list.splice(idx, 1)
      },


      save() {
        const promises = []

        this.list.forEach((el, idx) => {
          if(!el._changed) {
            return
          }

          let name
          let mutation
          let variables = {
            id: el.id,
            input: {
              type: el.type,
              lang: this.item.lang,
              label: this.title(el),
              data: JSON.stringify(el.data),
              files: el.files.map(f => f.id),
            }
          }

          if(el.id) {
            name = 'saveContent'
            mutation = gql`mutation($id: ID!, $input: ContentInput!) {
              saveContent(id: $id, input: $input) {
                id
              }
            }`
          } else {
            name = 'addContent'
            mutation = gql`mutation($input: ContentInput!) {
              addContent(input: $input) {
                id
              }
            }`
          }

          promises.push(this.$apollo.mutate({
            mutation: mutation,
            variables: variables
          }).then(response => {
            if(response.errors) {
              throw response.errors
            }

            if(response.data && response.data[name]?.id) {
              el.id = response.data[name]?.id
              el._changed = false
            }
          }).catch(error => {
            console.error(`save()`, el, mutation, variables, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.$emit('update:contents', this.list.map(el => el.id).filter(id => !!id))
        })
      },


      search(term) {
        this.list.forEach(el => {
          el._hide = term !== '' && !JSON.stringify(el).toLocaleLowerCase().includes(term)
        })
      },


      shown(el) {
        return (
          typeof el._hide === 'undefined' || typeof el._hide !== 'undefined' && el._hide !== true
        ) && (
          this.aside.isUsed('type', el.type)
        )
      },


      title(el) {
        return Object.keys(el.data)
          .map(key => el.data[key] && typeof el.data[key] !== 'object' ? el.data[key] : null)
          .filter(v => !!v)
          .join(' - ')
          .substring(0, 50) || el.label || ''
      },


      toggle() {
        this.list.forEach(el => {
          if(this.shown(el)) {
            el._checked = !el._checked
          }
        })
      },


      update(el, type, value) {
        el[type] = value
        el._changed = true
      },

      visibility(type) {
        this.aside.show['type'] = type ? true : false
      }
    },

    watch: {
      list() {
        const types = {}
        const status = {draft: 0, published: 0}

        this.list.forEach(el => {
          if(el.type) {
            types[el.type] = (types[el.type] || 0) + 1
          }
        })

        this.aside.store['type'] = types
      }
    }
  }
</script>

<template>
  <v-container v-observe-visibility="visibility">
    <v-sheet>

      <div class="header">
        <div class="bulk">
          <v-checkbox-btn v-model="checked" @click.stop="toggle()"></v-checkbox-btn>
          <v-menu location="bottom right">
            <template v-slot:activator="{ props }">
              <v-btn append-icon="mdi-menu-down" variant="outlined" v-bind="props">Actions</v-btn>
            </template>
            <v-list>
              <v-list-item>
                <v-btn prepend-icon="mdi-delete" variant="text" @click="purge()">Delete</v-btn>
              </v-list-item>
            </v-list>
          </v-menu>
        </div>

        <v-text-field
          prepend-inner-icon="mdi-magnify"
          variant="underlined"
          label="Search"
          class="search"
          clearable
          hide-details
          @input="search($event.target.value)"
          @click:clear="search('')"
        ></v-text-field>

        <div class="actions">
          <v-btn icon="mdi-history"
            :class="{hidden: !item.versions?.length}"
            @click="vhistory = true"
            variant="outlined"
            elevation="0"
          ></v-btn>
          <v-btn
            @click="save()"
            :color="changed ? 'primary' : ''"
            elevation="0"
          >Save</v-btn>
        </div>
      </div>

      <v-expansion-panels class="list" v-model="panel" elevation="0" multiple>
        <VueDraggable v-model="list" draggable=".content" group="content">

          <v-expansion-panel v-for="(el, idx) in list" :key="idx" v-show="shown(el)" class="content" :class="{changed: el._changed}">
            <v-expansion-panel-title expand-icon="mdi-pencil">
              <v-checkbox-btn v-model="el._checked"></v-checkbox-btn>

              <v-menu>
                <template v-slot:activator="{ props }">
                  <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
                </template>
                <v-list>
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-copy" variant="text" @click="copy(idx)">Copy</v-btn>
                  </v-list-item>
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-cut" variant="text" @click="cut(idx)">Cut</v-btn>
                  </v-list-item>
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(idx)">Insert before</v-btn>
                  </v-list-item>
                  <v-list-item>
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(idx + 1)">Insert after</v-btn>
                  </v-list-item>
                  <v-list-item v-if="clip">
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(idx)">Paste before</v-btn>
                  </v-list-item>
                  <v-list-item v-if="clip">
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(idx + 1)">Paste after</v-btn>
                  </v-list-item>
                  <v-list-item>
                    <v-btn prepend-icon="mdi-delete" variant="text" @click="remove(idx)">Delete</v-btn>
                  </v-list-item>
                </v-list>
              </v-menu>

              <div class="element-title">{{ title(el) }}</div>
              <div class="element-type">{{ el.type }}</div>
            </v-expansion-panel-title>
            <v-expansion-panel-text>

              <Fields
                :data="el.data"
                :assets="el.files"
                :fields="fields(el.type)"
                @update:data="update(el, 'data', $event)"
                @update:assets="update(el, 'files', $event)"
              />

            </v-expansion-panel-text>
          </v-expansion-panel>

        </VueDraggable>
      </v-expansion-panels>

      <div class="btn-group">
        <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true" elevation="0"></v-btn>
      </div>
    </v-sheet>
  </v-container>


  <Teleport to="body">
    <v-dialog v-model="velements" scrollable width="auto">
      <Elements type="content" @add="add($event, index)" />
    </v-dialog>
  </Teleport>

  <Teleport to="body">
    <v-dialog v-model="vhistory" scrollable width="auto">
      <History :data="clean()" :versions="item.versions || []" @use="use($event)" @hide="vhistory = false" />
    </v-dialog>
  </Teleport>

</template>

<style scoped>
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  margin-bottom: 2rem;
}

.header > * {
  margin: 0.5rem 0;
}

.actions button {
  margin-inline-start: 0.5rem;
}

.bulk {
  display: flex;
  align-items: center;
}

.v-input.search {
  max-width: 30rem;
}

.v-expansion-panel {
  border-inline-start: 3px solid transparent;
}

.v-expansion-panel.changed {
  border-inline-start: 3px solid rgb(var(--v-theme-warning));
}

.v-expansion-panel-title .v-selection-control {
  flex: none;
}
</style>
