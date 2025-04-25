<script>
  import { diffJson } from 'diff'

  export default {
    props: {
      'data': {type: Object, required: true},
      'contents': {type: Array, required: true},
      'versions': {type: Array, required: true},
    },

    emit: ['hide', 'use', 'revert'],

    data: () => ({
      list: [],
      show: false,
    }),

    mounted() {
      this.list = this.versions.map(v => {
        return {...v, data: JSON.parse(v.data), contents: JSON.parse(v.contents)}
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
          <v-timeline-item v-if="JSON.stringify(list[0]?.data) != JSON.stringify(data) || JSON.stringify(list[0]?.contents) != JSON.stringify(contents)" size="small" dot-color="blue">

            <v-card class="elevation-2" @click="show = !show">
              <v-card-title>Current</v-card-title>
              <v-card-text class="diff" :class="{show: show}">
                <div class="data">
                  <span v-for="part of diff(list[0]?.data || {}, data)"
                    :class="{added: part.added, removed: part.removed}">{{ part.value || part }}</span>
                </div>
                <div class="contents">
                  <span v-for="part of diff(list[0]?.contents || {}, contents)"
                    :class="{added: part.added, removed: part.removed}">{{ part.value || part }}</span>
                </div>
              </v-card-text>
              <v-card-actions>
                <v-btn variant="outlined" @click="$emit('revert', list[0])">
                  Revert
                </v-btn>
              </v-card-actions>
            </v-card>

          </v-timeline-item>

          <v-timeline-item v-for="(version, idx) in list.slice(1)" :key="idx" size="small"
            :dot-color="version.published ? 'success' : 'grey-lighten-1'">

            <v-card class="elevation-2">
              <div @click="version._show = !version._show">
                <v-card-title>{{ version.created_at }}</v-card-title>
                <v-card-subtitle>{{ version.editor }}</v-card-subtitle>
                <v-card-text class="diff" :class="{show: version._show}">
                  <div class="data">
                    <span v-for="part of diff(list[idx+2]?.data || version.data, version.data)"
                      :class="{added: part.added, removed: part.removed}">{{ part.value || part }}</span>
                  </div>
                  <div class="contents">
                    <span v-for="part of diff(list[idx+2]?.contents?.data || version.contents?.data, version.contents?.data)"
                      :class="{added: part.added, removed: part.removed}">{{ part.value || part }}</span>
                  </div>
                </v-card-text>
              </div>
              <v-card-actions>
                <v-btn variant="outlined" @click="$emit('use', version)">
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

  .v-timeline-item .v-card-text.diff span {
    white-space: pre;
  }

  .v-timeline-item:not(:last-child) .v-card-text.diff span {
    display: none;
  }

  .v-timeline-item .v-card-text.diff.show span {
    display: inline;
  }

  .v-timeline-item .v-card-text.diff span.added {
    background-color: #00ff0030;
    display: inline;
  }

  .v-timeline-item .v-card-text.diff span.removed {
    background-color: #ff000030;
    display: inline;
  }
</style>
