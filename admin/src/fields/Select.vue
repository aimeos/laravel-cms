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
  <v-select ref="field"
    :items="config.options || []"
    :placeholder="config.placeholder || ''"
    :multiple="config.multiple"
    :chips="config.multiple"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    :rules="[
      v => (!config.required || !!v) || `Value is required`,
    ]"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    item-title="label"
  ></v-select>
</template>
