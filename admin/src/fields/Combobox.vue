<script>
  import Autocomplete from './Autocomplete.vue'

  export default {
    extends: Autocomplete,
  }
</script>

<template>
  <v-combobox ref="field"
    :rules="[
      v => !config.required || !!v || `Value is required`,
    ]"
    :loading="loading"
    :readonly="readonly"
    :clearable="!readonly"
    :items="list || []"
    :no-data-text="config['empty-text'] || 'No data available'"
    :placeholder="config.placeholder || ''"
    :multiple="config.multiple"
    :chips="config.multiple"
    :modelValue="modelValue"
    @update:modelValue="update($event)"
    @update:search="search($event)"
    density="comfortable"
    hide-details="auto"
    variant="outlined"
    item-title="label"
    item-value="value"
  ></v-combobox>
</template>
