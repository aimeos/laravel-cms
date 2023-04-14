<script>
  export default {
    props: ['data'],
    emits: ['update:data'],
    data: () => ({
    })
  }
</script>

<template>
  <v-container>
    <v-row>
      <v-col cols="12" md="6">
        <v-text-field label="Text" variant="underlined"></v-text-field>
      </v-col>
    </v-row>
  </v-container>
</template>

<style scoped>
</style>
