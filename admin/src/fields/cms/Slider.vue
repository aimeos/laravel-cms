<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-slider
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  ></v-slider>
</template>
