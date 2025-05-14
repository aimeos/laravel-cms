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
      'assets': {type: Object, default: () => {}},
      'readonly': {type: Boolean, default: false},
    },

    emits: ['update:modelValue', 'error'],

    methods: {
      update(value) {
        this.$emit('update:modelValue', value)
        this.validate()
      },


      async validate() {
        await this.$nextTick()
        const errors = await this.$refs.field.validate()

        this.$emit('error', errors.length > 0)
        return !errors.length
      }
    }
  }
</script>

<template>
  <v-text-field ref="field"
    :readonly="readonly"
    :counter="config.max"
    :clearable="!readonly"
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
  ></v-text-field>
</template>
