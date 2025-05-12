<script>
  import { VDateInput } from 'vuetify/labs/VDateInput'

  export default {
    components: {
      VDateInput,
    },

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
  <v-date-input ref="field"
    :rules="[
      v => !config.required || !!v || `Value is required`,
    ]"
    :allowed-dates="config.allowed"
    :clearable="!config.required"
    :max="config.max"
    :min="config.min"
    :multiple="config.multiple"
    :placeholder="config.placeholder || null"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    show-adjacent-months
  ></v-date-input>
</template>
