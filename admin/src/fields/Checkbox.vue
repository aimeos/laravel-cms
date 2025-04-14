<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-checkbox
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  ></v-checkbox>
</template>
