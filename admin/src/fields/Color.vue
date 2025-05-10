<script>
  export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue', 'error'],

    methods: {
      update(value) {
        this.$emit('update:modelValue', value)
      }
    }
  }
</script>

<template>
  <v-color-picker
    :modelValue="modelValue"
    @update:modelValue="update($event)"
  ></v-color-picker>
</template>
