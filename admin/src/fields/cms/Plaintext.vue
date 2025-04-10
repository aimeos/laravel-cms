<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue']
  }
</script>

<template>
  <v-textarea
    :label="config.label || ''"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    variant="underlined"
    clearable
  ></v-textarea>
</template>
