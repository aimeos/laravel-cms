<script>
  export default {
    props: {
      'modelValue': {type: Boolean, default: false},
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
  <v-switch
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    hide-details="auto"
    color="primary"
    inset
  ></v-switch>
</template>
