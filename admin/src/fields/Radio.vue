<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-radio-group
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    hide-details="auto"
  ><v-radio v-for="(label, value) in (config.options || [])"
      :label="label"
      :value="value">
    </v-radio>
  </v-radio-group>
</template>
