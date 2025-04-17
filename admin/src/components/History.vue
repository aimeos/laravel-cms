<script>
  import { diffJson } from 'diff'

  export default {
    props: ['data', 'versions'],

    emit: ['hide', 'use'],

    data: () => ({
      list: [],
      show: false,
    }),

    mounted() {
      this.list = this.versions.map(v => {
        return {...v, data: JSON.parse(v.data)}
      }).reverse()
    },

    methods: {
      diff(old, str) {
        if(old && str) {
          return diffJson(old, str)
        } else if(str) {
          return [str]
        }
        return []
      }
    }
  }
</script>

<template>
    <v-card prepend-icon="mdi-history">
      <template v-slot:append>
        <v-icon @click="$emit('hide')">mdi-close</v-icon>
      </template>
      <template v-slot:title>
        History
      </template>

      <v-divider></v-divider>

      <v-card-text>
        <v-timeline side="end" align="start">
          <v-timeline-item v-if="JSON.stringify(list[0]?.data) != JSON.stringify(data)" size="small" dot-color="blue">

            <v-card class="elevation-2" @click="show = !show">
              <v-card-title>Current</v-card-title>
              <v-card-text :class="{show: show}">
                <span v-for="part of diff(list[0]?.data || {}, data)"
                  :class="{added: part.added, removed: part.removed}">{{ part.value || part }}</span>
              </v-card-text>
              <v-card-actions>
                <v-btn variant="outlined" @click="$emit('use', list[0]?.data)">
                  Revert
                </v-btn>
              </v-card-actions>
            </v-card>

          </v-timeline-item>

          <v-timeline-item v-for="(version, idx) in list" :key="idx" size="small"
            :dot-color="version.published ? 'success' : 'grey-lighten-1'">

            <v-card class="elevation-2" @click="version._show = !version._show">
              <v-card-title>{{ version.created_at }}</v-card-title>
              <v-card-subtitle>{{ version.editor }}</v-card-subtitle>
              <v-card-text :class="{show: version._show}">
                <span v-for="part of diff(list[idx+1]?.data || version.data, version.data)"
                  :class="{added: part.added, removed: part.removed}">
                  {{ part.value || part }}
                </span>
              </v-card-text>
              <v-card-actions>
                <v-btn variant="outlined" @click="$emit('use', version.data)">
                  Use
                </v-btn>
              </v-card-actions>
            </v-card>

          </v-timeline-item>
        </v-timeline>
      </v-card-text>

      <v-divider></v-divider>

      <v-card-actions>
        <v-btn block @click="$emit('hide')">
          Close
        </v-btn>
      </v-card-actions>
    </v-card>
</template>

<style scoped>
  .v-timeline--vertical {
      grid-template-columns: 0 min-content auto;
  }

  .v-timeline-item__opposite {
    display: none;
  }

  /* todo: Doesn't work when display:contents is used */
  .v-timeline-item__body {
    justify-self: auto !important;
  }

  .v-timeline-item .v-card-text > span {
    white-space: pre;
    display: none;
  }

  .v-timeline-item .v-card-text.show > span {
    display: inline;
  }

  .v-timeline-item .v-card-text > span.added {
    background-color: #00ff0030;
    display: inline;
  }

  .v-timeline-item .v-card-text > span.removed {
    background-color: #ff000030;
    display: inline;
  }
</style>
