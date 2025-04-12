<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-select
    :items="config.options || []"
    :placeholder="config.placeholder || ''"
    :multiple="config.multiple"
    :chips="config.multiple"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    :rules="[
      v => (!config.required || !!v) || `Value is required`,
    ]"
    density="comfortable"
    variant="underlined"
    item-title="label"
  ></v-select>
</template>
