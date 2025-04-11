<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
    methods: {
      validate(v) {
        return v && this.config.required
          ? /^(https?:\/\/)?([\da-z.-]+)\.([a-z.]{2,})([\/\w .-]*)*\/?$/.test(v)
          : true
      }
    }
  }
</script>

<template>
  <v-text-field
    :label="config.label || ''"
    :placeholder="config.placeholder || ''"
    :rules="[
      v => (config.required && !!v) || `Value is required`,
      v => validate(v) || `Not a valid URL`,
    ]"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    density="comfortable"
    variant="underlined"
    clearable
  ></v-text-field>
</template>
