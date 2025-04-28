<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue']
  }
</script>

<template>
  <v-text-field
    :counter="config.max"
    :placeholder="config.placeholder || ''"
    :rules="[
      v => (!config.max || v && v.length <= config.max) || `Maximum length is ${config.max} characters`,
      v => (!config.min || v && v.length >= config.min) || `Minimum length is ${config.min} characters`
    ]"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-text-field>
</template>
