<script>
  import Fields from './Fields.vue'
  import History from './History.vue'
  import Elements from './Elements.vue'
  import { useSideStore } from '../stores'
  import { VueDraggable } from 'vue-draggable-plus'

  export default {
    components: {
      Fields,
      History,
      Elements,
      VueDraggable
    },
    setup() {
      const aside = useSideStore()
      return { aside }
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
      elements: {
        'heading': {
          type: 'heading',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3,4H5V10H9V4H11V18H9V12H5V18H3V4M13,8H15.31L15.63,5H17.63L17.31,8H19.31L19.63,5H21.63L21.31,8H23V10H21.1L20.9,12H23V14H20.69L20.37,17H18.37L18.69,14H16.69L16.37,17H14.37L14.69,14H13V12H14.9L15.1,10H13V8M17.1,10L16.9,12H18.9L19.1,10H17.1Z" /></svg>',
          fields: {
            'level': {type: 'select', required: true, options: [
              {value: 1, label: 'H1'},
              {value: 2, label: 'H2'},
              {value: 3, label: 'H3'},
              {value: 4, label: 'H4'},
              {value: 5, label: 'H5'},
              {value: 6, label: 'H6'}
            ]},
            'text': {type: 'string', min: 1},
          }
        },
        'paragraph': {
          type: 'paragraph',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>',
          fields: {
            'text': {type: 'text'},
          }
        },
        'image-text': {
          type: 'image-text',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M7 4.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0m-.861 1.542 1.33.886 1.854-1.855a.25.25 0 0 1 .289-.047l1.888.974V7.5a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V7s1.54-1.274 1.639-1.208M5 9a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/></svg>',
          fields: {
            'image': {type: 'image'},
            'text': {type: 'text'},
          }
        },
        'article': {
          type: 'article',
          group: 'content',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>',
          fields: {
            'title': {type: 'string'},
            'plain': {type: 'plaintext'},
            'intro': {type: 'text'},
            'text': {type: 'markdown'},
            'cover': {type: 'image'},
            'gallery': {type: 'images'},
          }
        },
        'table': {
          type: 'table',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z"/></svg>',
          fields: {
            'title': {type: 'string'},
            'content': {type: 'table'},
            'header': {type: 'select', options: [
              {value: '', label: 'None'},
              {value: 'row', label: 'First row'},
              {value: 'col', label: 'First column'},
              {value: 'row+col', label: 'First row and column'},
            ]},
          }
        },
      }
    }),
    mounted() {
      this.contents = JSON.parse(this.item.versions[0] ? this.item.versions[0].data : this.item.data || '[]')
    },
    methods: {
      add(code, idx) {
        if(!this.elements[code]) {
          console.error(`Element not found "${code}"`)
          return
        }

        const entry = Object.assign(JSON.parse(JSON.stringify(this.elements[code])), {data: {}})

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

      search(term) {
        this.contents.forEach(el => {
          el._hide = term !== '' && !JSON.stringify(el).toLocaleLowerCase().includes(term)
        })
      },

      show(content) {
        return (
          typeof content._hide === 'undefined' || typeof content._hide !== 'undefined' && content._hide !== true
        ) && (
          this.aside.isUsed('type', content.type)
        )
      },

      title(content) {
        return Object.values(content.data || {}).filter(v => typeof v !== 'object' && !!v).join(' - ').substring(0, 50) || content.label || ''
      },

      toggle() {
        this.contents.forEach(el => {
          el._checked = !el._checked
        })
      },

      visibility(isVisible) {
        this.aside.show['type'] = isVisible ? true : false
      }
    },
    watch: {
      contents: {
        deep: true,
        handler() {
          const types = {}

          this.contents.forEach((el) => {
            if(el.type) {
              types[el.type] = (types[el.type] || 0) + 1
            }
          })

          this.aside.store['type'] = types
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

        <v-text-field prepend-inner-icon="mdi-magnify" label="Search" variant="underlined" class="search"
          clearable hide-details @input="search($event.target.value)" @click:clear="search('')">
        </v-text-field>

        <v-btn icon="mdi-history"
          :class="{hidden: !item.versions.length}"
          @click="vhistory = true"
          variant="outlined"
          elevation="0"
        ></v-btn>
      </div>

      <v-expansion-panels class="list" v-model="panel" multiple>
        <VueDraggable v-model="contents" draggable=".content" group="content">

          <v-expansion-panel v-for="(content, idx) in contents" :key="idx" v-show="show(content)" class="content" elevation="1" rounded="lg">
            <v-expansion-panel-title expand-icon="mdi-pencil">
              <v-checkbox-btn v-model="content._checked"></v-checkbox-btn>

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

              <div class="element-title">{{ title(content) }}</div>
              <div class="element-type">{{ content.type }}</div>
            </v-expansion-panel-title>
            <v-expansion-panel-text>

              <Fields :fields="content.fields" v-model:data="content.data" />

            </v-expansion-panel-text>
          </v-expansion-panel>

        </VueDraggable>
      </v-expansion-panels>

      <div class="btn-group">
        <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true"></v-btn>
      </div>
    </v-sheet>
  </v-container>


  <Teleport to="body">
    <v-dialog v-model="velements" scrollable width="auto">
      <Elements :ce="elements" :index="index" @add="add($event.type, $event.index)" />
    </v-dialog>
  </Teleport>

  <Teleport to="body">
    <v-dialog v-model="vhistory" scrollable width="auto">
      <History name="data" :data="clean()" :versions="item.versions || []" @use="use($event)" @hide="vhistory = false" />
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
}

.v-expansion-panel-title .v-selection-control {
    flex: none;
  }
</style>
