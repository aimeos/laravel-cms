<script>
  export default {
    props: ['ce', 'index'],
    emits: ['add'],
    data: () => ({
      panel: [0]
    })
  }
</script>

<template>
  <v-container>
    <v-expansion-panels v-model="panel">

      <v-expansion-panel elevation="1">
        <v-expansion-panel-title>
          Available elements
        </v-expansion-panel-title>
        <v-expansion-panel-text>

          <v-btn v-for="(data, code) in ce" :key="code" variant="text" stacked
            @click="$emit('add', {type: code, index: index})">
            <template v-slot:prepend>
                <span class="icon" v-html="data.icon"></span>
            </template>
            {{ code }}
          </v-btn>

        </v-expansion-panel-text>
      </v-expansion-panel>

    </v-expansion-panels>
  </v-container>
</template>

<style scoped>
.icon {
  width: 3rem;
}
</style>
