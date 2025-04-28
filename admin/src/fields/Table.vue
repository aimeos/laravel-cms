<script>
  export default {
    props: ['modelValue', 'config'],
    emits: ['update:modelValue'],
    methods: {
      validate(value) {
        let lines = 0
        let columns = 0

        value.split('\n').forEach(line => {
          columns += line.split(';').length
          lines++
        })

        return Number.isInteger(columns / lines)
      }
    }
  }
</script>

<template>
  <v-textarea
    placeholder="val;val;val
val;val;val"
    :auto-grow="true"
    :rules="[
      v => !!v || 'This field is required',
      v => validate(v) || 'Invalid format'
    ]"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event.replace(/[\r\n]+/g, '\n').replace(/^\n+|\n+$/g, ''))"
    variant="outlined"
    hide-details="auto"
    density="comfortable"
    clearable
  ></v-textarea>
</template>
