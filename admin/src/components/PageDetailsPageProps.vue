<script>
  import { useLanguageStore } from '../stores'
  import { useAppStore } from '../stores'

  export default {
    setup() {
      const languages = useLanguageStore()
      const app = useAppStore()
      return { app, languages }
    },
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
    }),
    computed: {
      langs() {
        const list = [{code: '', name: 'None'}]

        Object.entries(this.languages.available).forEach(pair => {
          list.push({code: pair[0], name: pair[1]})
        })

        return list
      }
    },
    watch: {
      item: {
        deep: true,
        handler() {
          this.$emit('update:item', this.item)
        }
      }
    }
  }
</script>

<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="6">
        <v-select v-model="item.status" label="Status" :items="[
          { key: 0, val: 'Disabled' },
          { key: 1, val: 'Enabled' },
          { key: 2, val: 'Hidden in navigation' }
        ]" item-title="val" item-value="key" variant="underlined" required></v-select>
      </v-col>
      <v-col cols="12" md="6">
        <v-select v-model="item.lang" label="Language" :items="langs" item-title="name" item-value="code" variant="underlined"></v-select>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-text-field v-model="item.name" label="Page name" variant="underlined" counter="30" required></v-text-field>
        <v-text-field v-model="item.title" label="Page title" variant="underlined" counter="70"></v-text-field>
      </v-col>
      <v-col cols="12" md="6">
        <v-text-field v-model="item.slug" label="URL segment" variant="underlined" counter="255" required></v-text-field>
        <v-text-field v-model="item.domain" label="Domain" variant="underlined"></v-text-field>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-text-field v-model="item.tag" label="Page tag" variant="underlined"></v-text-field>
      </v-col>
      <v-col cols="12" md="6">
        <v-select v-model="item.cache" label="Cache time" :items="[
          { key: 0, val: 'No cache' },
          { key: 1, val: '1 minute' },
          { key: 5, val: '5 minutes' },
          { key: 15, val: '15 minutes' },
          { key: 30, val: '30 minutes' },
          { key: 60, val: '1 hour' },
          { key: 180, val: '3 hours' },
          { key: 360, val: '6 hours' },
          { key: 720, val: '12 hours' },
          { key: 1440, val: '24 hours' },
        ]" item-title="val" item-value="key" variant="underlined"></v-select>
      </v-col>
      <v-col cols="12">
        <v-text-field v-model="item.to" label="Target URL" variant="underlined"></v-text-field>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-text-field type="datetime-local" v-model="item.start" label="Start date"
          variant="underlined"></v-text-field>
      </v-col>
      <v-col cols="12" md="6">
        <v-text-field type="datetime-local" v-model="item.end" label="End date"
          variant="underlined"></v-text-field>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
</style>
