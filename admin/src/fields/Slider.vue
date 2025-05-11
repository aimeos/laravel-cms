<script>
  export default {
    props: {
      'modelValue': {type: Number, default: 0},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue', 'error'],

    methods: {
      update(value) {
        this.$emit('update:modelValue', value)
      },


      validate() {
        return Promise.resolve(true)
      }
    }
  }
</script>

<template>
  <v-slider
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    hide-details="auto"
  ></v-slider>
</template>
