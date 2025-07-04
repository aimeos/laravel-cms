<script>
  import gql from 'graphql-tag'
  import { useAppStore, useAuthStore, useConfigStore, useLanguageStore, useSideStore } from '../stores'

  export default {
    props: {
      'item': {type: Object, required: true},
      'assets': {type: Object, default: () => {}},
    },

    emits: ['change', 'error'],

    inject: ['debounce'],

    data: () => ({
      errors: {},
      messages: {}
    }),

    setup() {
      const languages = useLanguageStore()
      const config = useConfigStore()
      const side = useSideStore()
      const auth = useAuthStore()
      const app = useAppStore()

      return { app, auth, side, config, languages }
    },

    created() {
      this.checkPathd = this.debounce(this.checkPath, 500)
    },

    computed: {
      langs() {
        const list = [{code: null, name: 'None'}]

        Object.entries(this.languages.available).forEach(pair => {
          list.push({code: pair[0], name: pair[1]})
        })

        return list
      },


      readonly() {
        return !this.auth.can('page:save')
      }
    },

    methods: {
      checkPath() {
        return this.$apollo.query({
          query: gql`query($filter: PageFilter) {
            pages(filter: $filter) {
              data {
                id
              }
            }
          }`,
          variables: {
            filter: {
              path: this.item.path || '',
              domain: this.item.domain || '',
            }
          }
        }).then(result => {
          if(result?.data?.pages?.data?.length > 0 && result?.data?.pages?.data?.some(page => page.id !== this.item.id)) {
            this.messages.path = ['The path is already in use by another page']
          } else {
            this.messages.path = []
          }

          this.$emit('error', !!this.messages.path.length)
          return this.messages.path
        }).catch(error => {
          console.error('PageDetailItemProps::checkPath: Error checking path', error)
        })
      },


      reset() {
        this.errors = {}
      },


      setPath(focused) {
        if(!focused && this.item.path?.at(0) === '_') {
          this.item.path = this.item.name?.replace(/[ ]+/g, '-')?.toLowerCase()
        }
      },


      update(what, value) {
        this.item[what] = value.trim()
        this.$emit('change', true)
      },


      async validate(lazy = false) {
        await this.$nextTick()
        const list = [lazy ? this.checkPathd() : this.checkPath()]

        Object.values(this.$refs).forEach(field => {
          list.push(field.validate())
        })

        return Promise.all(list).then(result => {
          const res = result.reduce((sum, r) => sum + r.length, 0)
          this.$emit('error', !!res)
          return res || true
        });
      }
    },

    watch: {
      item: {
        deep: true,
        immediate: true,
        handler(val) {
          this.validate(true)
        }
      }
    }
  }
</script>

<template>
  <v-container>
    <v-sheet>

      <v-row>
        <v-col cols="12" md="6">
          <v-select ref="status"
            :items="[
              { key: 0, val: 'Disabled' },
              { key: 1, val: 'Enabled' },
              { key: 2, val: 'Hidden in navigation' }
            ]"
            :readonly="readonly"
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
            :readonly="readonly"
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
            :readonly="readonly"
            :modelValue="item.name"
            @update:modelValue="update('name', $event)"
            @update:focused="setPath($event)"
            variant="underlined"
            label="Page name"
            counter="255"
            maxlength="255"
          ></v-text-field>
          <v-text-field ref="title"
            :readonly="readonly"
            :modelValue="item.title"
            @update:modelValue="update('title', $event)"
            variant="underlined"
            label="Page title"
            counter="255"
            maxlength="255"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field ref="path"
            :rules="[
              v => !v || v && v[0] !== '/' || `Path must not start with a slash (/)`,
            ]"
            :error="!!(messages.path || []).length"
            :error-messages="messages.path"
            :readonly="readonly"
            :modelValue="item.path"
            @update:modelValue="update('path', $event); messages.path = null"
            @change="checkPath()"
            variant="underlined"
            label="URL path"
            counter="255"
            maxlength="255"
          ></v-text-field>
          <v-text-field ref="domain"
            :rules="[
              v => !v || v && /^([0-9a-z]+[.-])*[0-9a-z]+\.[a-z]{2,}$/.test(v) || `Domain name is invalid`,
            ]"
            :readonly="readonly"
            :modelValue="item.domain"
            @update:modelValue="update('domain', $event)"
            variant="underlined"
            label="Domain"
            counter="255"
            maxlength="255"
          ></v-text-field>
        </v-col>
      </v-row>

      <v-row>
        <v-col cols="12" md="6">
          <v-select ref="theme"
            :readonly="readonly"
            :items="Object.keys(config.get('themes', {'cms': ''}))"
            :modelValue="item.theme"
            @update:modelValue="update('theme', $event); item.type = ''"
            variant="underlined"
            label="Theme"
          ></v-select>
          <v-select ref="type"
            :readonly="readonly"
            :items="Object.keys(config.get(`themes.${item.theme || 'cms'}.types`, {'default': ''}))"
            :modelValue="item.type"
            @update:modelValue="update('type', $event)"
            variant="underlined"
            label="Page type"
          ></v-select>
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field ref="tag"
            v-model="item.tag"
            :readonly="readonly"
            label="Page tag"
            variant="underlined"
            @update:modelValue="update()"
            counter="30"
            maxlength="30"
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
            :readonly="readonly"
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
            :readonly="readonly"
            :modelValue="item.to"
            @update:modelValue="update('to', $event)"
            variant="underlined"
            label="Redirect URL"
            counter="255"
            maxlength="255"
          ></v-text-field>
        </v-col>
      </v-row>

    </v-sheet>
  </v-container>
</template>

<style scoped>
</style>
