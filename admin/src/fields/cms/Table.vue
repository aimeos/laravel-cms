<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-textarea v-if="field.type === 'table'"
    placeholder="val;val;val
val;val;val"
    :auto-grow="true"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    variant="underlined"
    density="comfortable"
    clearable
  ></v-textarea>
</template>
