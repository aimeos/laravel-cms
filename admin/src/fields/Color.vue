<script>
  export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue'],

    methods: {
      validate() {
        return Promise.resolve([])
      }
    }
  }
</script>

<template>
  <v-color-picker
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  ></v-color-picker>
</template>
