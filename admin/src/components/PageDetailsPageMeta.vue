<script>
  import Elements from './Elements.vue'
  import History from './History.vue'
  import Fields from './Fields.vue'

  export default {
    components: {
      Elements,
      History,
      Fields,
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      elements: {
        'meta': {
          type: 'meta',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" /></svg>',
          fields: {
            'description': {type: 'string', min: 1, max: 180},
            'keywords': {type: 'string'},
          }
        },
        'social': {
          type: 'social',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z" /></svg>',
          fields: {
            'og:url': {type: 'url'},
            'og:title': {type: 'string'},
          },
        },
        'canonical': {
          type: 'canonical',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5,6.41L6.41,5L17,15.59V9H19V19H9V17H15.59L5,6.41Z" /></svg>',
          fields: {
            'url': {type: 'url', required: true},
          }
        }
      },
      meta: {},
      velements: false,
      vhistory: false,
      panel: []
    }),
    mounted() {
      this.meta = JSON.parse(this.item.versions[0]?.meta || this.item.meta || '{}')
    },
    computed: {
      available() {
        return Object.keys(this.elements).length
      },

      history() {
        return this.vhistory ? true : false
      }
    },
    methods: {
      add(code) {
        if(!this.elements[code]) {
          console.error(`Element not found "${code}"`)
          return
        }

        if(this.meta[code]) {
          return
        }

        this.meta[code] = Object.assign(JSON.parse(JSON.stringify(this.elements[code])), {data: {}})
        this.panel.push(Object.keys(this.meta).length - 1)
        this.velements = false
      },

      remove(code) {
        delete this.meta[code]
      },

      title(el) {
        return Object.values(el.data || {}).filter(v => typeof v !== 'object' && !!v).join(' - ').substring(0, 50) || el.label || ''
      },

      use(data) {
        this.meta = data
        this.vhistory = null
      }
    },
    watch: {
      meta: {
        deep: true,
        handler() {
          const item = {...this.item}
          item.meta = this.meta
          this.$emit('update:item', item)
        }
      }
    }
  }
</script>

<template>
  <v-sheet>
    <div class="header">
      <v-btn icon="mdi-history"
        :class="{hidden: !item.versions.length}"
        @click="vhistory = true"
        variant="outlined"
        elevation="0"
      ></v-btn>
    </div>

    <v-expansion-panels class="list" v-model="panel" multiple>

      <v-expansion-panel v-for="(el, code) in meta || {}" :key="code" elevation="1" rounded="lg">
        <v-expansion-panel-title expand-icon="mdi-pencil">
          <v-btn icon="mdi-delete" variant="text" @click="remove(code)"></v-btn>
          <div class="element-title">{{ title(el) }}</div>
          <div class="element-type">{{ el.type }}</div>
        </v-expansion-panel-title>
        <v-expansion-panel-text>
          <Fields :fields="el.fields" v-model:data="el.data" />
        </v-expansion-panel-text>
      </v-expansion-panel>

    </v-expansion-panels>

    <div v-if="available" class="btn-group">
      <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true"></v-btn>
    </div>
  </v-sheet>

  <Teleport to="body">
    <v-dialog v-model="velements" scrollable width="auto">
      <Elements :ce="elements" @add="add($event.type)" />
    </v-dialog>
  </Teleport>

  <Teleport to="body">
    <v-dialog v-model="vhistory" scrollable width="auto">
      <History name="meta" :data="meta" :versions="item.versions || []" @use="use($event)" @hide="vhistory = false" />
    </v-dialog>
  </Teleport>
</template>

<style scoped>
  .header {
    display: flex;
    justify-content: end;
  }
</style>
