<script>
  import gql from 'graphql-tag'
  import Fields from './Fields.vue'
  import SchemaDialog from './SchemaDialog.vue'
  import { VueDraggable } from 'vue-draggable-plus'
  import { useAuthStore, useMessageStore, useSchemaStore, useSideStore } from '../stores'
  import { uid } from '../utils'

  export default {
    components: {
      Fields,
      SchemaDialog,
      VueDraggable
    },

    props: {
      'item': {type: Object, required: true},
      'assets': {type: Object, required: true},
      'content': {type: Array, required: true},
      'elements': {type: Object, required: true},
      'section': {type: [String, null], default: null}
    },

    emits: ['error', 'update:content'],

    data: () => ({
      panel: [],
      menu: {},
      clip: null,
      index: null,
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
        return this.content.some(el => el._changed)
      }
    },

    methods: {
      add(item, idx) {
        let entry = {
          id: uid(),
          group: this.section || 'main'
        }

        if(item.id) {
          this.elements[item.id] = item
          entry = Object.assign(entry, {type: 'reference', refid: item.id})
        } else {
          entry = Object.assign(entry, {type: item.type, data: {}})
        }

        if(idx !== null) {
          this.content.splice(idx, 0, entry)
          this.panel.push(this.panel.includes(idx) ? idx + 1 : idx)
        } else {
          this.content.push(entry)
          this.panel.push(this.content.length - 1)
        }

        this.$emit('update:content', this.content)
        this.vschemas = false
        this.store()
      },


      copy(idx) {
        const entry = JSON.parse(JSON.stringify(this.content[idx]))
        entry['id'] = null

        this.clip = {type: 'copy', index: idx, content: entry}
      },


      cut(idx) {
        this.clip = {type: 'cut', index: idx, content: this.content[idx]}
        this.content.splice(idx, 1)
        this.store()
      },


      error(el, value) {
        el._error = value
        this.$emit('error', this.content.some(el => el._error))
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
        this.content.splice(idx, 0, this.clip.content)
        this.$emit('update:content', this.content)
        this.clip = null
        this.store()
      },


      purge() {
        for(let i = this.content.length - 1; i >= 0; i--) {
          if(this.content[i]._checked) {
            this.content.splice(i, 1)
          }
        }

        this.$emit('update:content', this.content)
        this.checked = false
        this.store()
      },


      remove(idx) {
        this.content.splice(idx, 1)
        this.$emit('error', this.content.some(el => el._error))
        this.$emit('update:content', this.content)
        this.store()
      },


      reset() {
        this.content.forEach(el => {
          delete el._changed
          delete el._error
        })

        this.store()
      },


      search(term) {
        if(term) {
          term = term.toLocaleLowerCase().trim()

          this.content.forEach(el => {
            const item = (el.type === 'reference') ? this.elements[el.refid] || {} : el
            el._hide = !JSON.stringify(Object.values(item?.data || {})).toLocaleLowerCase().includes(term)
          })
        }
      },


      share(idx) {
        if(!this.auth.can('element:add')) {
          this.messages.add(this.$gettext('Permission denied'), 'error')
          return
        }

        const entry = this.content[idx]

        if(!entry) {
          this.messages.add(this.$gettext('Element not found'), 'error')
          return
        }

        if(entry.type === 'reference') {
          this.messages.add(this.$gettext('Element is already shared'), 'error')
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
                  lang
                  mime
                  name
                  path
                  previews
                  description
                  updated_at
                  editor
                }
              }
            }
          `,
          variables: {
            input: {
              type: entry.type,
              lang: this.item.lang,
              name: this.title(entry),
              data: JSON.stringify(entry.data || {}),
            },
            files: entry.files?.filter((fileid, idx, self) => {
              return self.indexOf(fileid) === idx
            }) || []
          }
        }).then(result => {
          if(result.errors) {
            throw result.errors
          }

          const element = result.data.addElement

          for(const file of element.files || []) {
            file.previews = JSON.parse(file.previews || '{}')
            this.assets[file.id] = file
          }

          element.data = JSON.parse(element.data)
          element.files = element.files.map(file => file.id)

          this.elements[element.id] = element
          this.content[idx] = {id: uid(), group: this.section || 'main', type: 'reference', refid: element.id}
          this.$emit('update:content', this.content)
          this.store()
        }).catch(error => {
          this.messages.add(this.$gettext('Unable to make element shared'), 'error')
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

        this.content.forEach(el => {
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

        return this.side.store = {type: types, state: state}

        // for translation only
        this.$gettext('type')
        this.$gettext('state')
        this.$gettext('valid')
        this.$gettext('changed')
        this.$gettext('error')
      },


      title(el) {
        return (el.data?.title || el.data?.text || Object.values(el.data || {})
          .map(v => v && typeof v !== 'object' && typeof v !== 'boolean' ? v : null)
          .filter(v => !!v)
          .join(' - '))
          .substring(0, 50) || el.type || ''
      },


      toggle() {
        this.content.forEach(el => {
          if(this.shown(el)) {
            el._checked = !el._checked
          }
        })
      },


      unshare(idx) {
        if(!this.content[idx]) {
          this.messages.add(this.$gettext('Content element not found'), 'error')
          return
        }

        const entry = this.content[idx]

        if(entry.type !== 'reference' || !this.elements[entry.refid]) {
          this.messages.add(this.$gettext('Element is not shared'), 'error')
          return
        }

        for(const file of this.elements[entry.refid].files || []) {
          this.assets[file.id] = file
        }

        this.content[idx].type = this.elements[entry.refid].type || null
        this.content[idx].data = this.elements[entry.refid].data || {}
        delete this.content[idx].refid

        this.$emit('update:content', this.content)
        this.store()
      },


      update(el) {
        el._changed = true
        el.group = this.section || 'main'

        this.$emit('error', this.content.some(el => el._error))
        this.$emit('update:content', this.content)
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
    }
  }
</script>

<template>
  <div v-observe-visibility="store">

    <div class="header">
      <div v-if="auth.can('page:save')" class="bulk">
        <v-checkbox-btn v-model="checked" @click.stop="toggle()"></v-checkbox-btn>
        <v-menu location="bottom right">
          <template v-slot:activator="{ props }">
            <v-btn append-icon="mdi-menu-down" variant="text" v-bind="props">{{ $gettext('Actions') }}</v-btn>
          </template>
          <v-list>
            <v-list-item>
              <v-btn prepend-icon="mdi-delete" variant="text" @click="purge()">{{ $gettext('Delete') }}</v-btn>
            </v-list-item>
          </v-list>
        </v-menu>
      </div>

      <v-text-field
        prepend-inner-icon="mdi-magnify"
        variant="underlined"
        :label="$gettext('Search for')"
        class="search"
        clearable
        hide-details
        @input="search($event.target.value)"
        @click:clear="search('')"
      ></v-text-field>
    </div>

    <v-expansion-panels class="list" v-model="panel" elevation="0" multiple>
      <VueDraggable
        @update:modelValue="$emit('update:content', $event)"
        :disabled="!auth.can('page:save')"
        :modelValue="content"
        draggable=".content"
        group="content">

        <v-expansion-panel v-for="(el, idx) in content" :key="idx" v-show="shown(el)" class="content" :class="{changed: el._changed, error: el._error}">
          <v-expansion-panel-title expand-icon="mdi-pencil">
            <v-checkbox-btn v-if="auth.can('page:save')" v-model="el._checked" @click.stop=""></v-checkbox-btn>

            <v-menu v-if="auth.can('page:save')">
              <template v-slot:activator="{ props }">
                <v-btn icon="mdi-dots-vertical" variant="text" v-bind="props"></v-btn>
              </template>
              <v-list>
                <v-list-item v-if="!el._error">
                  <v-btn prepend-icon="mdi-content-copy" variant="text" @click="copy(idx)">{{ $gettext('Copy') }}</v-btn>
                </v-list-item>
                <v-list-item v-if="!el._error">
                  <v-btn prepend-icon="mdi-content-cut" variant="text" @click="cut(idx)">{{ $gettext('Cut') }}</v-btn>
                </v-list-item>

                <v-divider></v-divider>

                <v-list-item v-if="!el._error && clip">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(idx)">🠕 {{ $gettext('Paste before') }}</v-btn>
                </v-list-item>
                <v-list-item v-if="!el._error && clip">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="paste(idx + 1)">🠗 {{ $gettext('Paste after') }}</v-btn>
                </v-list-item>
                <v-list-item v-if="!el._error">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(idx)">🠕 {{ $gettext('Insert before') }}</v-btn>
                </v-list-item>
                <v-list-item v-if="!el._error">
                  <v-btn prepend-icon="mdi-content-paste" variant="text" @click="insert(idx + 1)">🠗 {{ $gettext('Insert after') }}</v-btn>
                </v-list-item>

                <v-divider></v-divider>

                <v-list-item>
                  <v-btn prepend-icon="mdi-delete" variant="text" @click="remove(idx)">{{ $gettext('Delete') }}</v-btn>
                </v-list-item>
                <v-list-item v-if="!el._error && el.type !== 'reference' && auth.can('element:add')">
                  <v-btn prepend-icon="mdi-link" variant="text" @click="share(idx)">{{ $gettext('Make shared') }}</v-btn>
                </v-list-item>
                <v-list-item v-if="el.type === 'reference'">
                  <v-btn prepend-icon="mdi-link-off" variant="text" @click="unshare(idx)">{{ $gettext('Merge copy') }}</v-btn>
                </v-list-item>
              </v-list>
            </v-menu>

            <v-icon v-if="el.type === 'reference'" class="icon-shared" icon="mdi-link" :title="$gettext('Shared element')"></v-icon>

            <div class="element-title">{{ el.type === 'reference' ? elements[el.refid]?.name : title(el) }}</div>
            <div class="element-type">{{ $gettext(el.type) }}</div>
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
        variant="flat"
      ></v-btn>
    </div>
  </div>


  <Teleport to="body">
    <SchemaDialog v-model="vschemas" @add="add($event, index)" />
  </Teleport>

</template>

<style scoped>
.header {
  margin-top: 8px;
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
  margin-inline-end: 4px;
}
</style>
