<script>
  import Content from './Content.vue'
  import History from './History.vue'
  import Elements from './Elements.vue'
  import { useSideStore } from '../stores'

  export default {
    components: {
      Content,
      History,
      Elements
    },
    setup() {
      const aside = useSideStore()
      return { aside }
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      contents: [],
      menu: {},
      side: {},
      clip: null,
      index: null,
      checked: false,
      vhistory: false,
      velements: false,
      elements: {
        'cms::heading': {
          type: 'heading',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3,4H5V10H9V4H11V18H9V12H5V18H3V4M13,8H15.31L15.63,5H17.63L17.31,8H19.31L19.63,5H21.63L21.31,8H23V10H21.1L20.9,12H23V14H20.69L20.37,17H18.37L18.69,14H16.69L16.37,17H14.37L14.69,14H13V12H14.9L15.1,10H13V8M17.1,10L16.9,12H18.9L19.1,10H17.1Z" /></svg>',
          fields: {
            'level': {type: 'select', options: [
              {value: 1, label: 'H1'},
              {value: 2, label: 'H2'},
              {value: 3, label: 'H3'},
              {value: 4, label: 'H4'},
              {value: 5, label: 'H5'},
              {value: 6, label: 'H6'}
            ]},
            'text': {type: 'string'},
          }
        },
        'cms::paragraph': {
          type: 'paragraph',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>',
          fields: {
            'text': {type: 'markdown'},
          }
        },
        'cms::image-text': {
          type: 'image-text',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M7 4.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0m-.861 1.542 1.33.886 1.854-1.855a.25.25 0 0 1 .289-.047l1.888.974V7.5a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V7s1.54-1.274 1.639-1.208M5 9a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/></svg>',
          fields: {
            'image': {type: 'image'},
            'text': {type: 'markdown'},
          }
        },
        'cms::list': {
          type: 'list',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path fill-rule="evenodd" d="M5 11.5a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h9a.5.5 0 0 1 0 1h-9a.5.5 0 0 1-.5-.5"/><path d="M2.242 2.194a.27.27 0 0 1 .516 0l.162.53c.035.115.14.194.258.194h.551c.259 0 .37.333.164.493l-.468.363a.28.28 0 0 0-.094.3l.173.569c.078.256-.213.462-.423.3l-.417-.324a.27.27 0 0 0-.328 0l-.417.323c-.21.163-.5-.043-.423-.299l.173-.57a.28.28 0 0 0-.094-.299l-.468-.363c-.206-.16-.095-.493.164-.493h.55a.27.27 0 0 0 .259-.194zm0 4a.27.27 0 0 1 .516 0l.162.53c.035.115.14.194.258.194h.551c.259 0 .37.333.164.493l-.468.363a.28.28 0 0 0-.094.3l.173.569c.078.255-.213.462-.423.3l-.417-.324a.27.27 0 0 0-.328 0l-.417.323c-.21.163-.5-.043-.423-.299l.173-.57a.28.28 0 0 0-.094-.299l-.468-.363c-.206-.16-.095-.493.164-.493h.55a.27.27 0 0 0 .259-.194zm0 4a.27.27 0 0 1 .516 0l.162.53c.035.115.14.194.258.194h.551c.259 0 .37.333.164.493l-.468.363a.28.28 0 0 0-.094.3l.173.569c.078.255-.213.462-.423.3l-.417-.324a.27.27 0 0 0-.328 0l-.417.323c-.21.163-.5-.043-.423-.299l.173-.57a.28.28 0 0 0-.094-.299l-.468-.363c-.206-.16-.095-.493.164-.493h.55a.27.27 0 0 0 .259-.194z"/></svg>',
          fields: {
            'list': {type: 'list'},
            'type': {type: 'select', options: [
              {value: 'ul', label: 'Bullets'},
              {value: 'ol', label: 'Numbered'},
            ]},
          }
        },
        'cms::article': {
          type: 'article',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>',
          fields: {
            'title': {type: 'string'},
            'intro': {type: 'markdown'},
            'cover': {type: 'image'},
            'gallery': {type: 'images'},
          }
        },
        'cms::table': {
          type: 'table',
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
        } else {
          this.contents.push(entry)
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
    <v-sheet elevation="0">

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

        <v-btn :class="{hidden: !item.versions.length}" variant="outlined" @click="vhistory = true">
          History
        </v-btn>
      </div>

      <v-expansion-panels>
        <Content v-for="(content, idx) in contents"
          :key="idx"
          :clip="clip"
          v-model:content="contents[idx]"
          @copy="copy(idx)"
          @cut="cut(idx)"
          @insert="insert(idx + $event)"
          @paste="paste(idx + $event)"
          @remove="remove(idx)"
          v-show="show(content)"
        />
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
.v-expansion-panel:nth-of-type(2n+1) .v-expansion-panel-title {
    background-color: #FAFAFA !important;
  }
.header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0 1rem;
  margin: 0 0.5rem 1rem;
}

.bulk {
  display: flex;
  align-items: center;
}

.v-expansion-panel {
  border-inline-start: 3px solid inherit !important;
  border-radius: 0;
}

.v-expansion-panel-title__overlay {
  background-color: transparent !important;
}

.v-expansion-panel--active {
  border-color: #000080 !important
}

.panel-heading {
  overflow: hidden;
}

.subtext {
  display: block;
  overflow: hidden;
  max-width: 25rem;
  max-height: 1.75rem;
  padding-top: 0.25rem;
  text-overflow: ellipsis;
  white-space: nowrap;
  color: #808080;
  font-size: 80%;
}

.btn-group {
  padding: 1rem 0;
}

.v-input.search {
  max-width: 30rem;
}
</style>
