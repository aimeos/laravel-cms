<script>
  export default {
    props: {
      'modelValue': {type: Number, default: 0},
      'config': {type: Object, default: () => {}},
      'readonly': {type: Boolean, default: false},
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
  <v-slider
    :readonly="readonly"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    hide-details="auto"
  ></v-slider>
</template>
