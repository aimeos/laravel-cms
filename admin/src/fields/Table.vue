<script>
  export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue', 'error'],

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


      update(value) {
        this.$emit('update:modelValue', value.replace(/[\r\n]+/g, '\n').replace(/^\n+|\n+$/g, ''))
        this.$refs.field.validate().then(errors => {
          this.$emit('error', errors.length > 0)
        })
      }
    }
  }
</script>

<template>
  <v-textarea ref="field"
    :rules="[
      v => !config.required || !!v || 'This field is required',
      v => !config.min || +v?.split('\n')[0]?.split(';')?.length >= +config.min || `Minimum are ${config.min} columns`,
      v => check(v) || 'The number of columns is not the same in all rows',
    ]"
    :auto-grow="true"
    :placeholder="config.placeholder || `val;val;val\nval;val;val`"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    variant="outlined"
    hide-details="auto"
    density="comfortable"
    clearable
  ></v-textarea>
</template>
