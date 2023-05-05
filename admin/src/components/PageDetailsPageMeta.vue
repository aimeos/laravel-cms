<script>
  import Elements from './Elements.vue'
  import Element from './Element.vue'
  import History from './History.vue'

  export default {
    components: {
      Elements,
      Element,
      History
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      ce: {
        'cms::meta': {type: 'cms::meta', 'description': {type: 'cms::string'}, 'keywords': {type: 'cms::string'}, icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" /></svg>'},
        'cms::social': {type: 'cms::social', 'og:url': {type: 'cms::string'}, 'og:title': {type: 'cms::string'}, icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z" /></svg>'},
        'cms::canonical': {type: 'cms::canonical', 'url': {type: 'cms::string'}, icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5,6.41L6.41,5L17,15.59V9H19V19H9V17H15.59L5,6.41Z" /></svg>'}
      },
      meta: {},
      velements: false,
      vhistory: false,
      panel: [0]
    }),
    mounted() {
      this.meta = JSON.parse(this.item.versions[0]?.meta || this.item.meta || '{}')
    },
    computed: {
      available() {
        return Object.keys(this.elements).length
      },

      elements() {
        const list = {...this.ce};
        for(const key in this.meta) {
          delete list[key];
        }
        return list
      },

      history() {
        return this.vhistory ? true : false
      }
    },
    methods: {
      add(code) {
        this.meta[code] = {type: code}
        this.velements = false
      },

      remove(code) {
        delete this.meta[code]
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
  <div class="header">
    <v-btn :class="{hidden: !item.versions.length}" variant="outlined" @click="vhistory = true">
      History
    </v-btn>
  </div>

  <v-expansion-panels>

    <v-expansion-panel v-for="(el, code) in meta || {}" :key="code" elevation="1">
      <v-expansion-panel-title collapse-icon="mdi-pencil">
        <v-btn icon="mdi-delete" variant="text" @click="remove(code)"></v-btn>
        <div class="panel-heading">
          {{ code }}
          <span class="subtext">{{ el.title || el.text || '' }}</span>
        </div>
      </v-expansion-panel-title>
      <v-expansion-panel-text>
        <Element :item="el" @update:item="meta[code] = $event" />
      </v-expansion-panel-text>
    </v-expansion-panel>

  </v-expansion-panels>

  <div v-if="available" class="btn-group">
    <v-btn icon="mdi-view-grid-plus" color="primary" @click="velements = true"></v-btn>
  </div>

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
  .v-expansion-panel:nth-of-type(2n+1) .v-expansion-panel-title {
    background-color: #ebf4ff;
  }

  .btn-group {
    padding: 1rem 0;
  }

  .header {
    display: flex;
    justify-content: end;
  }
</style>
