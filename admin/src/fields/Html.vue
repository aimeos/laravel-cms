<script>
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
  <v-textarea ref="field"
    :placeholder="config.placeholder || ''"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-textarea>
</template>
