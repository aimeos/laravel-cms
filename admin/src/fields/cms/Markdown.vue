<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue']
  }
</script>

<template>
  <v-textarea
    :label="config.label || ''"
    :auto-grow="true"
    :counter="true"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    variant="underlined"
    density="comfortable"
    clearable
  ></v-textarea>
</template>
