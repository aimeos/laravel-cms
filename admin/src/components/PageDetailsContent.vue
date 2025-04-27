<script>
  import gql from 'graphql-tag'
  import Fields from './Fields.vue'
  import History from './History.vue'
  import Elements from './Elements.vue'
  import { VueDraggable } from 'vue-draggable-plus'
  import { useMessageStore, useSchemaStore, useSideStore } from '../stores'

  export default {
    components: {
      Fields,
      History,
      Elements,
      VueDraggable
    },

    props: {
      'item': {type: Object, required: true},
      'contents': {type: Array, required: true},
      'elements': {type: Array, required: true}
    },

    emits: ['update:elements', 'update:contents'],

    data: () => ({
      list: [],
      panel: [],
      menu: {},
      side: {},
      clip: null,
      index: null,
      history: null,
      checked: false,
      vschemas: false,
      currentPage: 1,
      lastPage: 1,
    }),

    setup() {
      const messages = useMessageStore()
      const schemas = useSchemaStore()
      const sidestore = useSideStore()
      return { messages, schemas, sidestore }
    },

    computed: {
      changed() {
        return this.list.some(el => el._changed)
      }
    },

    methods: {
      add(type, idx) {
        if(!this.schemas.content[type]) {
          this.messages.add(`No schema definition for element "${type}"`, 'error')
          return
        }

        const entry = {type: type, data: {}, files: []}

        if(idx !== null) {
          this.list.splice(idx, 0, entry)
          this.panel.push(this.panel.includes(idx) ? idx + 1 : idx)
        } else {
          this.list.push(entry)
          this.panel.push(this.list.length - 1)
        }

        this.vschemas = false
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
        if(!this.schemas.content[type]?.fields) {
          console.warn(`No definition of fields for "${type}" schemas`)
          return []
        }

        return this.schemas.content[type]?.fields
      },


      insert(idx) {
        this.index = idx
        this.vschemas = true
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


      search(term) {
        this.list.forEach(el => {
          el._hide = term !== '' && !JSON.stringify(el).toLocaleLowerCase().includes(term)
        })
      },


      shown(el) {
        return (
          typeof el._hide === 'undefined' || typeof el._hide !== 'undefined' && el._hide !== true
        ) && (
          this.sidestore.isUsed('type', el.type) &&
          this.sidestore.isUsed('changed', el._changed || false)
        )
      },


      title(el) {
        return Object.values(el.data || {})
          .map(v => v && typeof v !== 'object' && typeof v !== 'boolean' ? v : null)
          .filter(v => !!v)
          .join(' - ')
          .substring(0, 50) || el.type || ''
      },


      toggle() {
        this.list.forEach(el => {
          if(this.shown(el)) {
            el._checked = !el._checked
          }
        })
      },


      updateStore() {
        const types = {}
        const changed = {}

        this.list.forEach(el => {
          if(el.type) {
            types[el.type] = (types[el.type] || 0) + 1
          }
          changed[Boolean(el._changed)] = (changed[Boolean(el._changed)] || 0) + 1
        })

        this.sidestore.store['type'] = types
        this.sidestore.store['changed'] = changed
      },


      use(data, idx, changed = true) {
        this.list[idx].data = data
        this.list[idx]._changed = changed
        this.history = null
      },


      visibility(type) {
        this.sidestore.show['type'] = type ? true : false
      }
    },

    watch: {
      contents: {
        immediate: true,
        handler() {
          this.list = this.contents
          this.updateStore()
        }
      },
      list: {
        deep: true,
        handler() {
          this.updateStore()
          this.$emit('update:contents', this.list)
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
      </div>

      <v-expansion-panels class="list" v-model="panel" elevation="0" multiple>
        <VueDraggable v-model="list" draggable=".content" group="contents">

          <v-expansion-panel v-for="(el, idx) in list" :key="idx" v-show="shown(el)" class="content" :class="{changed: el._changed}">
            <v-expansion-panel-title expand-icon="mdi-pencil">
              <v-checkbox-btn v-model="el._checked" @click.stop=""></v-checkbox-btn>

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
              <div class="actions">
                <v-btn v-if="el.versions?.length"
                  @click.stop="history = {data: el.data, index: idx, versions: el.versions}"
                  icon="mdi-history"
                  variant="flat"
                ></v-btn>
                <div v-else class="icon placeholder"></div>
              </div>
            </v-expansion-panel-title>
            <v-expansion-panel-text>

              <Fields
                :fields="fields(el.type)"
                v-model:data="el.data"
                v-model:assets="el.files"
                @change="el._changed = true"
              />

            </v-expansion-panel-text>
          </v-expansion-panel>

        </VueDraggable>
      </v-expansion-panels>

      <div class="btn-group">
        <v-btn icon="mdi-view-grid-plus" color="primary" @click="vschemas = true" elevation="0"></v-btn>
      </div>
    </v-sheet>
  </v-container>


  <Teleport to="body">
    <v-dialog v-model="vschemas" scrollable width="auto">
      <Elements type="content" @add="add($event, index)" />
    </v-dialog>
  </Teleport>

  <Teleport to="body">
    <v-dialog :modelValue="!!history" scrollable width="auto">
      <History
        :data="history?.data"
        :versions="history?.versions || []"
        @revert="use($event, history?.index, false)"
        @use="use($event, history?.index)"
        @hide="history = null"
      />
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
  background-color: transparent;
  margin-inline-start: 0.5rem;
}

.actions .icon.placeholder {
  height: 48px;
  width: 48px;
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

.element-type {
  word-wrap: break-word;
  overflow: hidden;
  max-height: 48px;
  min-width: 5rem;
}
</style>
