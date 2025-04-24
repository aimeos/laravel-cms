<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-switch
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    color="primary"
    inset
  ></v-switch>
</template>
