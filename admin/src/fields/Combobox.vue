<script>
  export default {
    props: {
      'modelValue': {type: Object, default: () => {}},
      'config': {type: Object, default: () => {}},
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
  <v-combobox ref="field"
    :items="config.options || []"
    :placeholder="config.placeholder || ''"
    :multiple="config.multiple"
    :chips="config.multiple"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    clearable
  ></v-combobox>
</template>
