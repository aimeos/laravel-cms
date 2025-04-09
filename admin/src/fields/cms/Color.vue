<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-color-picker
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  ></v-color-picker>
</template>
