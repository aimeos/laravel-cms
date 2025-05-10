<script>
  /**
   * Configuration:
   * - `max`: int, maximum number of characters allowed in the input field
   * - `min`: int, minimum number of characters required in the input field
   * - `placeholder`: string, placeholder text for the input field
   */
   export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue', 'error'],

    methods: {
      update(value) {
        this.$emit('update:modelValue', value)
        this.$refs.field.validate().then(errors => {
          this.$emit('error', errors.length > 0)
        })
      }
    }
  }
</script>

<template>
  <v-text-field ref="field"
    :counter="config.max"
    :placeholder="config.placeholder || ''"
    :rules="[
      v => !config.min || +v?.length >= +config.min || `Minimum length is ${config.min} characters`,
      v => !config.max || +v?.length <= +config.max || `Maximum length is ${config.max} characters`
    ]"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-text-field>
</template>
