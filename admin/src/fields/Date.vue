<script>
  import { VDateInput } from 'vuetify/labs/VDateInput'

  export default {
    components: {
      VDateInput,
    },

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
  <v-date-input ref="field"
    :rules="[
      v => (!config.required || config.required && v) || `Value is required`,
    ]"
    :allowed-dates="config.allowed"
    :clearable="!config.required"
    :max="config.max"
    :min="config.min"
    :multiple="config.multiple"
    :placeholder="config.placeholder || null"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    show-adjacent-months
  ></v-date-input>
</template>
