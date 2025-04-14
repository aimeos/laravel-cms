<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-date-picker
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    show-adjacent-months
  ></v-date-picker>
</template>
