<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-combobox
    :items="config.options || []"
    :placeholder="config.placeholder || ''"
    :multiple="config.multiple"
    :chips="config.multiple"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    variant="outlined"
    clearable
  ></v-combobox>
</template>
