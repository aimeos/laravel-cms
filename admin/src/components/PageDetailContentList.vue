<script>
  import gql from 'graphql-tag'
  import Schema from './Schema.vue'
  import Fields from './Fields.vue'
  import History from './History.vue'
  import { VueDraggable } from 'vue-draggable-plus'
  import { useAuthStore, useMessageStore, useSchemaStore, useSideStore } from '../stores'
  import { contentid } from '../utils'

  export default {
    components: {
      Schema,
      Fields,
      History,
      VueDraggable
    },

    props: {
      'section': {type: [String, null], default: null},
      'item': {type: Object, required: true},
      'contents': {type: Array, required: true},
      'elements': {type: Object, required: true},
      'assets': {type: Object, default: () => ({})}
    },

    emits: ['error', 'update:contents',  'update:elements'],

    data: () => ({
      list: [],
      panel: [],
      menu: {},
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
      const side = useSideStore()
      const auth = useAuthStore()

      return { auth, side, messages, schemas }
    },

    computed: {
      changed() {
        return this.list.some(el => el._changed)
      }
    },

    methods: {
      add(item, idx) {
        let entry = {
          cid: contentid(),
          group: this.section || 'main'
        }

        if(item.id) {
          entry = Object.assign(entry, {type: 'reference', refid: item.id})
          this.$emit('update:elements', Object.assign(this.elements, {[item.id]: item}))
        } else {
          entry = Object.assign(entry, {type: item.type, data: {}})
        }

        if(idx !== null) {
          this.list.splice(idx, 0, entry)
          this.panel.push(this.panel.includes(idx) ? idx + 1 : idx)
        } else {
          this.list.push(entry)
          this.panel.push(this.list.length - 1)
        }

        this.vschemas = false
        this.store()
      },


      clean(data) {
        for(const k in data) {
          if(k.startsWith('_')) {
            delete data[k]
          }
        }

        return data
      },


      copy(idx) {
        const entry = JSON.parse(JSON.stringify(this.list[idx]))
        entry['id'] = null

        this.clip = {type: 'copy', index: idx, content: entry}
      },


      cut(idx) {
        this.clip = {type: 'cut', index: idx, content: this.list[idx]}
        this.list.splice(idx, 1)
        this.store()
      },


      error(el, value) {
        el._error = value
        this.$emit('error', this.list.some(el => el._error))
        this.store()
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
        this.store()
      },


      purge() {
        for(let i = this.list.length - 1; i >= 0; i--) {
          if(this.list[i]._checked) {
            this.remove(i)
          }
        }
        this.checked = false
      },


      remove(idx) {
        this.list.splice(idx, 1)
        this.$emit('update:contents', this.list)
        this.$emit('error', this.list.some(el => el._error))
        this.store()
      },


      reset() {
        for(const el of this.list) {
          delete el._changed
          delete el._error
        }
      },


      search(term) {
        this.list.forEach(el => {
          el._hide = term !== '' && !JSON.stringify(el).toLocaleLowerCase().includes(term)
        })
      },


      share(idx) {
        if(!this.auth.can('element:add')) {
          this.messages.add('Permission denied', 'error')
          return
        }

        const entry = this.list[idx]

        if(!entry) {
          this.messages.add('Element not found', 'error')
          return
        }

        if(entry.type === 'reference') {
          this.messages.add('Element is already shared', 'error')
          return
        }

        this.$apollo.mutate({
          mutation: gql`
            mutation($input: ElementInput!, $files: [ID!]) {
              addElement(input: $input, files: $files) {
                id
                type
                lang
                name
                data
                editor
                updated_at
                files {
                  id
                }
              }
            }
          `,
          variables: {
            input: {
              type: entry.type,
              lang: this.item.lang,
              name: this.title(entry),
              data: JSON.stringify(this.clean(entry)),
            },
            files: entry.files.filter((fileid, idx, self) => {
              return self.indexOf(fileid) === idx
            })
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          const element = result.data.addElement
          element.data = JSON.parse(element.data)
          element.files = element.files.map(file => file.id)

          this.$emit('update:elements', Object.assign(this.elements, {[element.id]: element}))
          this.list[idx] = {cid: contentid(), group: this.section || 'main', type: 'reference', refid: element.id}
          this.store()
        }).catch(error => {
          this.messages.add('Unable to make element shared', 'error')
          this.$log(`PageDetailContentList::share(): Error making element shared`, idx, error)
        })
      },


      shown(el) {
        const valid = this.side.shown('state', 'valid')
        const error = this.side.shown('state', 'error')
        const changed = this.side.shown('state', 'changed')

        return !el._hide && this.side.shown('type', el.type) && (
          error && el._error || changed && el._changed || valid && !el._error && !el._changed
        )
      },


      store(isVisible = true) {
        if(!isVisible) {
          return
        }

        const types = {}
        const state = {}

        this.list.forEach(el => {
          if(el.type) {
            types[el.type] = (types[el.type] || 0) + 1
          }
          if(!el._changed && !el._error) {
            state['valid'] = (state['valid'] || 0) + 1
          }
          if(el._changed) {
            state['changed'] = (state['changed'] || 0) + 1
          }
          if(el._error) {
            state['error'] = (state['error'] || 0) + 1
          }
        })

        this.side.store = {type: types, state: state}
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


      unshare(idx) {
        if(!this.list[idx]) {
          this.messages.add('Content element not found', 'error')
          return
        }

        const entry = this.list[idx]

        if(entry.type !== 'reference' || !this.elements[entry.refid]) {
          this.messages.add('Element is not shared', 'error')
          return
        }

        for(const file of this.elements[entry.refid].files || []) {
          this.assets[file.id] = file
        }

        this.list[idx] = this.elements[entry.refid].data || {}
        this.store()
      },


      update(el) {
        el._changed = true
        el.group = this.section || 'main'

        this.$emit('update:contents', this.list)
        this.$emit('error', this.list.some(el => el._error))

        this.store()
      },


      use(data, idx, changed = true) {
        this.list[idx]._changed = changed
        this.list[idx].data = data

        this.history = null
        this.store()
      },


      validate() {
        const list = []

        this.$refs.field?.forEach(field => {
          list.push(field.validate())
        })

        return Promise.all(list).then(result => {
          return result.every(r => r)
        });
      }
    },

    watch: {
      contents: {
        immediate: true,
        handler(contents, old) {
          const a = (old || [])
          const b = (contents || [])

          if(a.length === b.length && a.every((el, idx) => el === b[idx])) {
            return
          }

          this.list = this.contents
          this.store()
        }
      },


      list: {
        handler(list) {
          this.$emit('update:contents', list)
        }
      }
    }
  }
</script>

<template>
  <v-container v-observe-visibility="store">
    <v-sheet>

      <div class="header">
        <div v-if="auth.can('page:save')" class="bulk">
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
          label="Search for"
          class="search"
          clearable
          hide-details
          @input="search($event.target.value)"
          @click:clear="search('')"
        ></v-text-field>
      </div>

      <v-expansion-panels class="list" v-model="panel" elevation="0" multiple>
        <VueDraggable v-model="list" :disabled="!auth.can('page:save')" draggable=".content" group="contents">

          <v-expansion-panel v-for="(el, idx) in list" :key="idx" v-show="shown(el)" class="content" :class="{changed: el._changed, error: el._error}">
            <v-expansion-panel-title expand-icon="mdi-pencil">
              <v-checkbox-btn v-if="auth.can('page:save')" v-model="el._checked" @click.stop=""></v-checkbox-btn>

              <v-menu v-if="auth.can('page:save')">
                <template v-slot:activator="{ props }">
                  <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
                </template>
                <v-list>
                  <v-list-item v-if="!el._error">
                    <v-btn prepend-icon="mdi-content-copy" variant="text" @click="copy(idx)">Copy</v-btn>
                  </v-list-item>
                  <v-list-item v-if="!el._error">
                    <v-btn prepend-icon="mdi-content-cut" variant="text" @click="cut(idx)">Cut</v-btn>
                  </v-list-item>
                  <v-list-item v-if="!el._error">
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(idx)">Insert before</v-btn>
                  </v-list-item>
                  <v-list-item v-if="!el._error">
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(idx + 1)">Insert after</v-btn>
                  </v-list-item>
                  <v-list-item v-if="!el._error && clip">
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(idx)">Paste before</v-btn>
                  </v-list-item>
                  <v-list-item v-if="!el._error && clip">
                    <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(idx + 1)">Paste after</v-btn>
                  </v-list-item>
                  <v-list-item>
                    <v-btn prepend-icon="mdi-delete" variant="text" @click="remove(idx)">Delete</v-btn>
                  </v-list-item>
                  <v-list-item v-if="!el._error && el.type !== 'reference' && auth.can('element:add')">
                    <v-btn prepend-icon="mdi-link" variant="text" @click="share(idx)">Make shared</v-btn>
                  </v-list-item>
                  <v-list-item v-if="el.type === 'reference'">
                    <v-btn prepend-icon="mdi-link-off" variant="text" @click="unshare(idx)">Merge copy</v-btn>
                  </v-list-item>
                </v-list>
              </v-menu>

              <v-icon v-if="el.type === 'reference'" class="icon-shared" icon="mdi-link" title="Shared element"></v-icon>

              <div class="element-title">{{ el.type === 'reference' ? elements[el.refid]?.name : title(el) }}</div>
              <div class="element-type">{{ el.type }}</div>
            </v-expansion-panel-title>
            <v-expansion-panel-text>

              <Fields v-if="el.type === 'reference'"
                :data="elements[el.refid]?.data || {}"
                :fields="fields(elements[el.refid]?.type)"
                :assets="assets"
                :readonly="true"
              />
              <Fields v-else ref="field"
                v-model:data="el.data"
                v-model:files="el.files"
                :readonly="!auth.can('page:save')"
                :fields="fields(el.type)"
                :assets="assets"
                @error="error(el, $event)"
                @change="update(el)"
              />

            </v-expansion-panel-text>
          </v-expansion-panel>

        </VueDraggable>
      </v-expansion-panels>

      <div v-if="auth.can('page:save')" class="btn-group">
        <v-btn @click="vschemas = true"
          icon="mdi-view-grid-plus"
          color="primary"
          elevation="0"
        ></v-btn>
      </div>
    </v-sheet>
  </v-container>


  <Teleport to="body">
    <v-dialog v-model="vschemas" scrollable width="100%">
      <Schema @add="add($event, index)" />
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

.bulk {
  display: flex;
  align-items: center;
}

.v-input.search {
  max-width: 30rem;
  flex-grow: 1;
  width: 100%;
  margin: auto;
}

.v-input.search > * {
  width: 100%;
}

.v-expansion-panel {
  border-inline-start: 3px solid transparent;
}

.v-expansion-panel.changed {
  border-inline-start: 3px solid rgb(var(--v-theme-warning));
}

.v-expansion-panel.error .v-expansion-panel-title {
  color: rgb(var(--v-theme-error));
}

.v-expansion-panel-title .v-selection-control {
  flex: none;
}

.element-type {
  max-height: 48px;
  max-width: 5rem;
  text-align: end;
}

.icon-shared {
  color: rgb(var(--v-theme-warning));
  margin-inline-end: 0.5rem;
}
</style>
