<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-switch
    :label="config.label || ''"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    inset
  ></v-switch>
</template>
