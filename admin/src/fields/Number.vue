<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-number-input
    :placeholder="config.placeholder || ''"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    variant="outlined"
  ></v-number-input>
</template>
