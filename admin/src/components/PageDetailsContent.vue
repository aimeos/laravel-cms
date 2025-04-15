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
    setup() {
      const elements = useElementStore()
      const aside = useSideStore()
      return { aside, elements }
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      contents: [],
      panel: [],
      menu: {},
      side: {},
      clip: null,
      index: null,
      checked: false,
      vhistory: false,
      velements: false,
    }),
    mounted() {
      this.contents = this.item.contents.map(el => {
        const entry = {...this.elements.content[el.type] || {}}

        entry.data = JSON.parse(el.versions[0]?.data || '{}')
        entry.files = el.versions[0]?.files || []
        entry.created_at = el.versions[0]?.created_at || null
        entry.editor = el.versions[0]?.editor || null

        return entry
      })
    },
    computed: {
      changed() {
        return this.contents.some(el => el._changed)
      }
    },
    methods: {
      add(item, idx) {
        const entry = Object.assign(item, {data: {}, files: []})

        if(idx !== null) {
          this.contents.splice(idx, 0, entry)
          this.panel.push(this.panel.includes(idx) ? idx + 1 : idx)
        } else {
          this.contents.push(entry)
          this.panel.push(this.contents.length - 1)
        }

        this.velements = false
      },

      clean() {
        this.contents.forEach(c => {
          delete c._checked
          delete c._hide
        })
        return this.contents
      },

      copy(idx) {
        const entry = JSON.parse(JSON.stringify(this.contents[idx]))
        entry['id'] = null

        this.clip = {type: 'copy', index: idx, content: entry}
      },

      cut(idx) {
        this.clip = {type: 'cut', index: idx, content: this.contents[idx]}
        this.contents.splice(idx, 1)
      },

      insert(idx) {
        this.index = idx
        this.velements = true
      },

      paste(idx) {
        this.contents.splice(idx, 0, this.clip.content)
        this.clip = null
      },

      purge() {
        for(let i = this.contents.length - 1; i >= 0; i--) {
          this.contents[i]._checked ? this.remove(i) : null
        }
        this.checked = false
      },

      remove(idx) {
        this.contents.splice(idx, 1)
      },

      save() {
        this.contents.forEach((el, idx) => {
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

          this.$apollo.mutate({
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
          })
        })
      },

      search(term) {
        this.contents.forEach(el => {
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
        return Object.keys(el.fields)
          .map(key => el.data[key] && typeof el.data[key] !== 'object' ? el.data[key] : null)
          .filter(v => !!v)
          .join(' - ')
          .substring(0, 50) || el.label || ''
      },

      toggle() {
        this.contents.forEach(el => {
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
      contents: {
        deep: true,
        handler() {
          const types = {}

          this.contents.forEach(el => {
            if(el.type) {
              types[el.type] = (types[el.type] || 0) + 1
            }
          })

          this.aside.store['type'] = types

          this.$emit('update:item', {...this.item, contents: this.contents})
        }
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
            :class="{hidden: !item.versions.length}"
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
        <VueDraggable v-model="contents" draggable=".content" group="content">

          <v-expansion-panel v-for="(el, idx) in contents" :key="idx" v-show="shown(el)" class="content" :class="{changed: el._changed}">
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
                :fields="el.fields"
                :data="el.data"
                :assets="el.files"
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
      <History :data="clean()" :versions="item.contents?.versions || []" @use="use($event)" @hide="vhistory = false" />
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
