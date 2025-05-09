<script>
  export default {
    props: {
      'modelValue': {type: Array, default: () => []},
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
  <v-range-slider
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    hide-details="auto"
  ></v-range-slider>
</template>
