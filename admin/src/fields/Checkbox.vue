<script>
  export default {
    props: {
      'modelValue': {type: Boolean, default: false},
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
  <v-checkbox
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    hide-details="auto"
  ></v-checkbox>
</template>
