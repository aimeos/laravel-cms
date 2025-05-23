<script>
  import gql from 'graphql-tag'


  export default {
    props: {
      'item': {type: Object, required: true}
    },

    emits: [],

    data: () => ({
      file: {},
      panel: [0, 1, 2],
      versions: {}
    }),

    watch: {
      item: {
        immediate: true,
        handler(item) {
          if(!item.id) {
            return
          }

          this.$apollo.query({
            query: gql`query ($id: ID!) {
              file(id: $id) {
                id
                pages {
                  id
                  slug
                  name
                }
                elements {
                  id
                  type
                  name
                }
                versions {
                  versionable_id
                  versionable_type
                  data
                }
              }
            }`,
            variables: {
              id: item.id,
            }
          }).then(result => {
            if(result.errors) {
              throw result.errors
            }

            this.file = result.data?.file || {}
            this.versions = (result.data?.file?.versions || []).map(item => {
              return {
                id: item.versionable_id,
                type: item.versionable_type,
                data: JSON.parse(item.data)
              }
            })
          }).catch(error => {
            console.error(`file(id: ${item.id})`, error)
          })

        }
      }
    }
  }
</script>

<template>
  <v-container>
    <v-sheet>
      <v-expansion-panels v-model="panel" elevation="0" multiple>

        <v-expansion-panel v-if="file.pages?.length">
          <v-expansion-panel-title>Pages</v-expansion-panel-title>
          <v-expansion-panel-text>
            <v-table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>URL</th>
                  <th>Name</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="v in file.pages" :key="v.id">
                  <td>{{ v.id }}</td>
                  <td>{{ v.slug }}</td>
                  <td>{{ v.name }}</td>
                </tr>
              </tbody>
            </v-table>
          </v-expansion-panel-text>
        </v-expansion-panel>

        <v-expansion-panel v-if="file.elements?.length">
          <v-expansion-panel-title>Elements</v-expansion-panel-title>
          <v-expansion-panel-text>
            <v-table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Name</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="v in file.elements" :key="v.id">
                  <td>{{ v.id }}</td>
                  <td>{{ v.type }}</td>
                  <td>{{ v.name }}</td>
                </tr>
              </tbody>
            </v-table>
          </v-expansion-panel-text>
        </v-expansion-panel>

        <v-expansion-panel v-if="versions?.length">
          <v-expansion-panel-title>Versions</v-expansion-panel-title>
          <v-expansion-panel-text>
            <v-table>
              <thead>
                <tr>
                  <th>ID</th>
                  <th>Type</th>
                  <th>Name</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="v in versions" :key="v.id">
                  <td>{{ v.id }}</td>
                  <td>{{ v.type }}</td>
                  <td>{{ v.data.name }}</td>
                </tr>
              </tbody>
            </v-table>
          </v-expansion-panel-text>
        </v-expansion-panel>

      </v-expansion-panels>
    </v-sheet>
  </v-container>
</template>

<style scoped>
</style>