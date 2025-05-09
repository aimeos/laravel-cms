<script>
  export default {
    props: {
      'modelValue': {type: String, default: ''},
      'config': {type: Object, default: () => {}},
    },

    emits: ['update:modelValue'],

    methods: {
      validate() {
        return Promise.resolve([])
      }
    }
  }
</script>

<template>
  <v-radio-group
    :modelValue="modelValue"
    @update:modelValue="$emit('update:modelValue', $event)"
    hide-details="auto"
  ><v-radio v-for="option in (config.options || [])"
      :label="option.label"
      :value="option.value">
    </v-radio>
  </v-radio-group>
</template>
