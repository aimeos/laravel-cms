<script>
  export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue', 'error'],

    methods: {
      update(value) {
        this.$emit('update:modelValue', value)
        this.validate()
      },


      validate() {
        return this.$refs.field.validate().then(errors => {
          this.$emit('error', errors.length > 0)
          return !errors.length
        })
      }
    }
  }
</script>

<template>
  <v-textarea ref="field"
    :placeholder="config.placeholder || ''"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    @update:focused="validate()"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-textarea>
</template>
