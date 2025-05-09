<script>
  /**
   * Configuration:
   * - `max`: int, maximum number of characters allowed in the input field
   * - `min`: int, minimum number of characters required in the input field
   * - `placeholder`: string, placeholder text for the input field
   */
   export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue'],

    methods: {
      validate() {
        return this.$refs.field.validate()
      }
    }
  }
</script>

<template>
  <v-text-field ref="field"
    :counter="config.max"
    :placeholder="config.placeholder || ''"
    :rules="[
      v => (!config.max || v && v.length <= config.max) || `Maximum length is ${config.max} characters`,
      v => (!config.min || v && v.length >= config.min) || `Minimum length is ${config.min} characters`
    ]"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-text-field>
</template>
