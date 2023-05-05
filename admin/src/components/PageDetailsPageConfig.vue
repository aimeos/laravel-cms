<script>
  export default {
    props: ['item'],
    emits: ['update:item'],
    data: () => ({
      panel: [0],
      elements: {
        'cms::test': {type: 'cms::test', data: {
          'test/key': {type: 'cms::string', label: 'Test string config'},
          'test/color': {type: 'cms::color', label: 'Test color selector'}
        }},
        'cms::test2': {type: 'cms::test2', data: {
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
              <v-col v-for="(def, key) in entry.data" cols="12" md="6">
                <v-text-field :label="def.label || 'Value'" variant="underlined">{{ item.config[key] || '' }}</v-text-field>
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
