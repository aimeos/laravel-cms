<script>
  export default {
    props: {
      'modelValue': {type: Boolean, default: false},
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
  <v-switch
    :readonly="readonly"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    hide-details="auto"
    color="primary"
    inset
  ></v-switch>
</template>
