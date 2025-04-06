<script>
  export default {
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      panel: [0],
      elements: {
        'cms::test': {type: 'cms::test', fields: {
          'test/key': {type: 'cms::string', label: 'Test string config'},
          'test/color': {type: 'cms::color', label: 'Test color selector'}
        }},
        'cms::test2': {type: 'cms::test2', fields: {
          'test2/key': {type: 'cms::string', label: 'Second string value'}
        }},
      }
    }),
  }
</script>

<template>
  <v-container>
    <v-expansion-panels v-model="panel">
      <v-expansion-panel v-for="(entry, code) in elements" :key="code" elevation="1">
        <v-expansion-panel-title>
          {{ code }}
        </v-expansion-panel-title>
        <v-expansion-panel-text>

          <v-container>
            <v-row>
              <v-col v-for="(def, key) in entry.fields" cols="12" md="6">
                <input :is="name(def.type)" :config="def" v-model="item.config[key]" />
              </v-col>
            </v-row>
          </v-container>

        </v-expansion-panel-text>
      </v-expansion-panel>

    </v-expansion-panels>
  </v-container>
</template>

<style scoped>
  .v-expansion-panel:nth-of-type(2n+1) .v-expansion-panel-title {
    background-color: #ebf4ff;
  }
</style>
