<script>
  import { useAppStore, useLanguageStore } from '../stores'

  export default {
    props: {
      'item': {type: Object, required: true}
    },

    emits: ['change', 'error'],

    data: () => ({
      errors: {}
    }),

    setup() {
      const languages = useLanguageStore()
      const app = useAppStore()
      return { app, languages }
    },

    computed: {
      langs() {
        const list = [{code: '', name: 'None'}]

        Object.entries(this.languages.available).forEach(pair => {
          list.push({code: pair[0], name: pair[1]})
        })

        return list
      }
    },

    methods: {
      reset() {
        this.errors = {}
      },


      update(what, value) {
        this.item[what] = value
        this.$emit('change', true)
        this.validate()
      },


      updateSlug(focused) {
        if(!focused && this.item.slug?.at(0) === '_') {
          this.item.slug = this.item.name?.replace(/[ ]+/g, '-')?.toLowerCase()
        }
      },


      async validate() {
        await this.$nextTick()
        const list = []

        Object.values(this.$refs).forEach(field => {
          list.push(field.validate())
        })

        return Promise.all(list).then(result => {
          const res = result.every(r => !r.length)
          this.$emit('error', !res)
          return res
        });
      }
    }
  }
</script>

<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="6">
        <v-select ref="status"
          :items="[
            { key: 0, val: 'Disabled' },
            { key: 1, val: 'Enabled' },
            { key: 2, val: 'Hidden in navigation' }
          ]"
          :modelValue="item.status"
          @update:modelValue="update('status', $event)"
          variant="underlined"
          item-title="val"
          item-value="key"
          label="Status"
        ></v-select>
      </v-col>
      <v-col cols="12" md="6">
        <v-select ref="lang"
          :items="langs"
          :modelValue="item.lang"
          @update:modelValue="update('lang', $event)"
          variant="underlined"
          item-title="name"
          item-value="code"
          label="Language"
        ></v-select>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-text-field ref="name"
          :rules="[
            v => !!v || `The field is required`,
          ]"
          :modelValue="item.name"
          @update:modelValue="update('name', $event)"
          @update:focused="updateSlug($event)"
          variant="underlined"
          label="Page name"
          counter="30"
        ></v-text-field>
        <v-text-field ref="title"
          :modelValue="item.title"
          @update:modelValue="update('title', $event)"
          variant="underlined"
          label="Page title"
          counter="70"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="6">
        <v-text-field ref="slug"
          :rules="[
            v => !!v || `The field is required`,
          ]"
          :modelValue="item.slug"
          @update:modelValue="update('slug', $event)"
          variant="underlined"
          label="URL path"
          counter="255"
        ></v-text-field>
        <v-text-field ref="domain"
          :modelValue="item.domain"
          @update:modelValue="update('domain', $event)"
          variant="underlined"
          label="Domain"
        ></v-text-field>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12" md="6">
        <v-text-field ref="theme"
          :modelValue="item.theme"
          @update:modelValue="update('theme', $event)"
          variant="underlined"
          label="Theme"
        ></v-text-field>
        <v-text-field ref="type"
          :modelValue="item.type"
          @update:modelValue="update('type', $event)"
          variant="underlined"
          label="Page type"
        ></v-text-field>
      </v-col>
      <v-col cols="12" md="6">
        <v-text-field ref="tag"
          v-model="item.tag"
          label="Page tag"
          variant="underlined"
          @update:modelValue="update()"
        ></v-text-field>
        <v-select ref="cache"
          :items="[
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
          ]"
          :modelValue="item.cache"
          @update:modelValue="update('cache', $event)"
          variant="underlined"
          label="Cache time"
          item-title="val"
          item-value="key"
        ></v-select>
      </v-col>
    </v-row>

    <v-row>
      <v-col cols="12">
        <v-text-field ref="to"
          :rules="[
            v => !v || v.match('^((https?:)?//([^\\s/:@]+(:[^\\s/:@]+)?@)?([0-9a-z]+(\\.|-))*[0-9a-z]+\\.[a-z]{2,}(:[0-9]{1,5})?)?(/[^\\s]*)*$') !== null || 'URL is not valid',
          ]"
          :modelValue="item.to"
          @update:modelValue="update('to', $event)"
          variant="underlined"
          label="Redirect URL"
        ></v-text-field>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
</style>
