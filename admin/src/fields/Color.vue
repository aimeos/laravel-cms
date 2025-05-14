<script>
  export default {
    props: {
      'modelValue': {type: String, default: ''},
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
  <v-color-picker
    :disabled="readonly"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
  ></v-color-picker>
</template>
