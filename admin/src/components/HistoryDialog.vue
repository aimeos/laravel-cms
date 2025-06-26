<script>
  import { diffJson } from 'diff'

  export default {
    props: {
      'modelValue': {type: Boolean, required: true},
      'current': {type: Object, required: true},
      'load': {type: Function, required: true},
    },

    emit: ['update:modelValue', 'use', 'revert'],

    data: () => ({
      list: [],
      show: false,
    }),

    mounted() {
      this.load().then(versions => {
        this.list = versions
      })
    },

    computed: {
      versions() {
        return this.list.filter(v => {
          return this.isModified(v, this.current) || v.published || v.publish_at
        })
      }
    },

    methods: {
      diff(old, str) {
        if(old && str) {
          return diffJson(old, str)
        } else if(str) {
          return [str]
        }
        return []
      },


      isModified(v1, v2) {
        if(!v1 || !v2) {
          return false
        }

        return (
          diffJson(v1.data || {}, v2.data || {}).length !== 1 ||
          diffJson(v1.content || {}, v2.content || {}).length !== 1
        )
      }
    }
  }
</script>

<template>
  <v-dialog :modelValue="modelValue" max-width="1200" scrollable>
    <v-card prepend-icon="mdi-history">
      <template v-slot:append>
        <v-icon @click="$emit('update:modelValue', false)">mdi-close</v-icon>
      </template>
      <template v-slot:title>
        History
      </template>

      <v-divider></v-divider>

      <v-card-text>
        <v-timeline side="end" align="start">
          <v-timeline-item v-if="isModified(list[0], current)" size="small" dot-color="blue">

            <v-card class="elevation-2" @click="show = !show">
              <v-card-title>Current</v-card-title>
              <v-card-text class="diff" :class="{show: show}">
                <span v-for="part of diff(list[0]?.data, current.data)" :class="{added: part.added, removed: part.removed}">
                  {{ part.value || part }}
                </span>
                <div v-if="current.content" class="divider">Content:</div>
                <span v-if="current.content" v-for="part of diff(list[0]?.content, current.content)" :class="{added: part.added, removed: part.removed}">
                  {{ part.value || part }}
                </span>
              </v-card-text>
              <v-card-actions>
                <v-btn variant="outlined" @click="$emit('revert', list[0])">
                  Revert
                </v-btn>
              </v-card-actions>
            </v-card>

          </v-timeline-item>

          <v-timeline-item v-for="(version, idx) in versions" :key="idx" size="small"
            :dot-color="version.published ? 'success' : 'grey-lighten-1'" :class="{publish: version.publish_at}">

            <v-card class="elevation-2">
              <div @click="version._show = !version._show">
                <v-card-title>{{ (new Date(version.publish_at || version.created_at)).toLocaleString() }}</v-card-title>
                <v-card-subtitle>{{ version.editor }}</v-card-subtitle>
                <v-card-text class="diff" :class="{show: version._show}">
                  <span v-for="part of diff(version.data, current.data)" :class="{added: part.removed, removed: part.added}">
                    {{ part.value || part }}
                  </span>
                  <div v-if="version.content" class="divider">Content:</div>
                  <span v-if="version.content" v-for="part of diff(version.content, current.content)" :class="{added: part.removed, removed: part.added}">
                    {{ part.value || part }}
                  </span>
                </v-card-text>
              </div>
              <v-card-actions>
                <v-btn variant="outlined" @click="$emit('use', version)">
                  Use version
                </v-btn>
              </v-card-actions>
            </v-card>

          </v-timeline-item>
        </v-timeline>
      </v-card-text>
    </v-card>
  </v-dialog>
</template>

<style scoped>
  .v-card {
    min-width: max(300px, 50vw);
  }

  .v-timeline--vertical {
      grid-template-columns: 0 min-content auto;
  }

  .v-timeline-item__opposite {
    display: none;
  }

  /* todo: Doesn't work when display:content is used */
  .v-timeline-item__body {
    justify-self: auto !important;
  }

  .v-timeline-item.publish .v-card-title {
    color: rgb(var(--v-theme-success));
  }

  .v-timeline-item .v-card-text.diff div.divider {
    margin: 1em 0 0.5em 0;
    font-weight: bold;
    font-size: 110%;
    display: none;
  }

  .v-timeline-item .v-card-text.diff.show div.divider {
    display: block;
  }

  .v-timeline-item .v-card-text.diff span {
    white-space: pre;
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
