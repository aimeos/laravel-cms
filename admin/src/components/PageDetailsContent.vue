<script>
  import Content from './Content.vue'
  import History from './History.vue'
  import Elements from './Elements.vue'
  import { useSideStore } from '../stores'

  export default {
    components: {
      Content,
      Elements,
      History
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
        'cms::text': {type: 'cms::text', icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>'},
        'cms::heading': {type: 'cms::heading', 'text': {type: 'cms::string'}, 'level': {type: 'cms::number'}, icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3,4H5V10H9V4H11V18H9V12H5V18H3V4M13,8H15.31L15.63,5H17.63L17.31,8H19.31L19.63,5H21.63L21.31,8H23V10H21.1L20.9,12H23V14H20.69L20.37,17H18.37L18.69,14H16.69L16.37,17H14.37L14.69,14H13V12H14.9L15.1,10H13V8M17.1,10L16.9,12H18.9L19.1,10H17.1Z" /></svg>'},
        'cms::article': {type: 'cms::article', 'title': {type: 'cms::string'}, 'intro': {type: 'cms::text'}, 'cover': {type: 'cms::image'}, icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>'},
      }
    }),
    mounted() {
      this.contents = JSON.parse(this.item.versions[0] ? this.item.versions[0].data : this.item.data || '[]')
    },
    methods: {
      add(code, idx) {
        if(idx !== null) {
          this.contents.splice(idx, 0, {type: code})
        } else {
          this.contents.push({type: code})
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
          :content="content" @update:content="contents[idx] = $event"
          v-model:checked="content._checked"
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
