<script>
  import { diffJson } from 'diff'

  export default {
    props: ['data', 'versions'],
    emit: ['hide', 'use'],
    data: () => ({
      list: [],
    }),
    mounted() {
      this.versions.forEach(v => {
        const item = {...v}
        item.data = JSON.parse(v.data || '{}')
        this.list.push(item)
      })
    },
    computed: {
      current() {
        const item = {...this.data}
        delete item.__typename
        delete item.versions
        return item
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
          <v-timeline-item v-if="versions[0]?.data != JSON.stringify(data)" size="small" dot-color="blue">

            <v-card class="elevation-2">
              <v-card-title>Current</v-card-title>
              <v-card-text>
                <span v-for="part of diff(list[0]?.data || {}, current)"
                  :class="{added: part.added, removed: part.removed}">{{ part.value || part }}</span>
              </v-card-text>
            </v-card>

          </v-timeline-item>

          <v-timeline-item v-for="(version, idx) in list" :key="idx" size="small"
            :dot-color="version.published ? 'success' : 'grey-lighten-1'">

            <v-card class="elevation-2">
              <v-card-title>{{ version.created_at }}</v-card-title>
              <v-card-subtitle>{{ version.editor }}</v-card-subtitle>
              <v-card-text>
                <span v-for="part of diff(list[idx+1]?.data || version.data, version.data)"
                  :class="{added: part.added, removed: part.removed}">{{ part.value || part }}</span>
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
  .v-timeline--vertical.v-timeline.v-timeline--side-end .v-timeline-item .v-timeline-item__body {
    padding-inline-start: 2.5%;
  }

  .v-dialog .v-overlay__content > .v-card > .v-card-item + .v-card-text {
      padding: 1rem 2.5%;
  }

  .v-timeline--vertical.v-timeline.v-timeline--side-end .v-timeline-item .v-timeline-item__opposite {
    display: none;
  }

  .v-timeline--vertical.v-timeline--justify-auto {
      grid-template-columns: 0 min-content auto;
  }

  .v-card-text {
    white-space: pre;
  }
  .added {
    background-color: #00ff0030
  }
  .removed {
    background-color: #ff000030
  }
</style>
