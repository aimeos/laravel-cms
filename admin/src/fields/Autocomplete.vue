<script>
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
  <v-autocomplete ref="field"
    :rules="[
      v => !config.required || !!v || `Value is required`,
    ]"
    :readonly="readonly"
    :items="config.options || []"
    :placeholder="config.placeholder || ''"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
  ></v-autocomplete>
</template>
