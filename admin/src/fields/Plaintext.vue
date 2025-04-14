<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue']
  }
</script>

<template>
  <v-textarea
    :counter="config.max"
    :placeholder="config.placeholder || ''"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    variant="outlined"
    clearable
  ></v-textarea>
</template>
