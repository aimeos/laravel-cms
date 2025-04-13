<script>
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
      this.contents = JSON.parse(this.item.versions[0] ? this.item.versions[0].data : this.item.data || '[]')
    },
    methods: {
      add(item, idx) {
        const entry = Object.assign(item, {data: {}})

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
      <Elements type="content" @add="add($event, index)" />
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
