<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
  }
</script>

<template>
  <v-range-slider
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
  ></v-range-slider>
</template>
