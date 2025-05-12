<script>
  export default {
    props: {
      'modelValue': {type: Array, default: () => []},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue', 'error'],

    methods: {
      update(value) {
        this.$emit('update:modelValue', value)
      },


      async validate() {
        return await true
      }
    }
  }
</script>

<template>
  <v-range-slider
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    hide-details="auto"
  ></v-range-slider>
</template>
