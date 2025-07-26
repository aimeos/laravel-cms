<script>
  import gql from 'graphql-tag'

  export default {
    props: {
      'modelValue': {type: [Object, String, Number, Boolean, null], default: null},
      'config': {type: Object, default: () => {}},
      'assets': {type: Object, default: () => {}},
      'readonly': {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'error'],

    inject: ['debounce'],

    data() {
      return {
        list: this.config.options || [],
        loading: false,
      }
    },

    created() {
      this.graphql = this.debounce(this.graphql, 500)
      this.rest = this.debounce(this.rest, 500)
    },

    methods: {
      graphql(value) {
        if(!this.config?.query) {
          return
        }

        const query = this.config.query.replace(/_term_/g, value ? JSON.stringify(value) : '""')

        this.loading = true
        this.$apollo.query({
          query: gql`${query}`,
        }).then(result => {
          this.list = this.items(this.toList(result.data))
          this.loading = false
        }).catch(error => {
          this.$log('Autocomplete::graphql(): Error fetching data', value, error)
        })
      },


      items(data) {
        if(!data) {
          return []
        }

        const flabel = this.config['item-title']
        const fvalue = this.config['item-value']

        return data.map(item => {
          if(typeof item === 'object' && item !== null) {
            if(flabel) {
              return {label: item[flabel] ?? '', value: item[fvalue] ?? ''}
            } else {
              return item[fvalue] ?? ''
            }
          } else {
            return item
          }
        })
      },


      rest(value) {
        if(!this.config?.url) {
          return
        }

        this.loading = true
        fetch(this.config.url.replace(/_term_/g, value ? value : ''), {
          mode: 'cors',
        }).then(response => {
          if(!response.ok) {
            throw response
          }
          return response.json()
        }).then(result => {
          this.list = this.items(this.toList(result))
          this.loading = false
        }).catch(error => {
          this.$log('Autocomplete::rest(): Error fetching data', value, error)
        })
      },


      search(value) {
        switch(this.config?.['api-type']) {
          case 'GQL': this.graphql(value); break
          case 'REST': this.rest(value); break
        }
      },


      toList(result) {
        if(this.config['list-key']) {
          return this.config['list-key'].split('/').reduce((part, key) => {
            return typeof part === 'object' && part !== null ? part[key] : part
          }, result)
        }

        return result
      },


      update(value) {
        this.$emit('update:modelValue', value)
        this.validate()
      },


      async validate() {
        await this.$nextTick()
        const errors = await this.$refs.field?.validate()

        this.$emit('error', errors?.length > 0)
        return !errors?.length
      }
    },

    watch: {
      modelValue: {
        immediate: true,
        handler(val) {
          this.validate()
        }
      }
    }
  }
</script>

<template>
  <v-autocomplete ref="field"
    :rules="[
      v => !config.required || !!v || $gettext('Value is required'),
    ]"
    :loading="loading"
    :readonly="readonly"
    :clearable="!readonly"
    :items="list"
    :no-data-text="config['empty-text'] || $gettext('No data available')"
    :placeholder="config.placeholder || ''"
    :return-object="!!config['item-title']"
    :multiple="config.multiple"
    :chips="config.multiple"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    @update:search="search($event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    item-title="label"
    item-value="value"
  ></v-autocomplete>
</template>
