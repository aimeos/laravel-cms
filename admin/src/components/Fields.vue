<script>
  export default {
    props: ['data', 'fields'],
    emits: ['update:data'],
  }
</script>

<template>
  <v-container>
    <v-row v-for="(field, code) in fields" :key="code">
      <v-col cols="12" sm="3" class="form-label">
        <v-label>{{ field.label || code }}</v-label>
      </v-col>
      <v-col cols="12" sm="9">
        <component :is="field.type?.charAt(0)?.toUpperCase() + field.type?.slice(1)"
          v-model="data[code]"
          :config="field"
        ></component>
      </v-col>
    </v-row>
  </v-container>
</template>
