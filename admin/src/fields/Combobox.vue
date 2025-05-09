<script>
  export default {
    props: {
      'modelValue': {type: Object, default: () => {}},
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
  <v-combobox ref="field"
    :items="config.options || []"
    :placeholder="config.placeholder || ''"
    :multiple="config.multiple"
    :chips="config.multiple"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-combobox>
</template>
