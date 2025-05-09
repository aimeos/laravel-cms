<script>
  export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue'],

    methods: {
      check(value) {
        let lines = 0
        let columns = 0

        value.split('\n').forEach(line => {
          columns += line.split(';').length
          lines++
        })

        return lines ? Number.isInteger(columns / lines) : true
      },


      validate() {
        return this.$refs.field.validate()
      }
    }
  }
</script>

<template>
  <v-textarea ref="field"
    :rules="[
      v => (!config.required || config.required && !!v) || 'This field is required',
      v => (!config.min || config.min && v?.split('\n')[0]?.split(';')?.length >= config.min) || `Minimum are ${config.min} columns`,
      v => check(v) || 'The number of columns is not the same in all rows',
    ]"
    :auto-grow="true"
    :placeholder="config.placeholder || `val;val;val\nval;val;val`"
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event.replace(/[\r\n]+/g, '\n').replace(/^\n+|\n+$/g, ''))"
    variant="outlined"
    hide-details="auto"
    density="comfortable"
    clearable
  ></v-textarea>
</template>
