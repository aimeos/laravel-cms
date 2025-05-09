<script>
  /**
   * Configuration:
   * - `max`: int, maximum number of characters allowed in the input field
   * - `min`: int, minimum number of characters required in the input field
   * - `placeholder`: string, placeholder text for the input field
   * - `required`: boolean, if true, the field is required
   * - `step`: int, step size for the number input
   */
   export default {
    props: {
      'modelValue': {type: Number, default: 0},
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
  <v-number-input ref="field"
    :rules="[
      v => (!config.required || config.required && v) || `Value is required`
    ]"
    :clearable="!config.required || true"
    :max="config.max"
    :min="config.min"
    :placeholder="config.placeholder || ''"
    :step="config.step || 1"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
  ></v-number-input>
</template>
