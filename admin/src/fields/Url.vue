<script>
  /**
   * Configuration:
   * - `allowed`: array of strings, allowed URL schemas (e.g., ['http', 'https'])
   * - `placeholder`: string, placeholder text for the input field
   * - `required`: boolean, if true, the field is required
   */
  export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue'],

    methods: {
      check(v) {
        const allowed = this.config.allowed || ['http', 'https']

        if(!allowed.every(s => /^[a-z]+/.test(s))) {
          return 'Invalid URL schema configuration'
        }

        return v || this.config.required
          ? (new RegExp(`^(${allowed.join('|')})://([^\\s/:@]+(:[^\\s/:@]+)?@)?([0-9a-z]+(\\.|-))*[0-9a-z]+\\.[a-z]{2,}(:[0-9]{1,5})?(/[^\\s]*)*$`)).test(v)
          : true
      },


      validate() {
        return this.$refs.field.validate()
      }
    }
  }
</script>

<template>
  <v-text-field ref="field"
    :placeholder="config.placeholder || ''"
    :rules="[
      v => (!config.required || config.required && v) || `Value is required`,
      v => check(v) || `Not a valid URL`,
    ]"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-text-field>
</template>
