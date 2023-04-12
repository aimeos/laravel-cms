<script>
  import gql from 'graphql-tag'
  import PageTree from '../components/PageTree.vue'
  import PageDetails from '../components/PageDetails.vue'

  export default {
    components: {
      PageTree,
      PageDetails
    },
    data: () => ({
      details: false,
      nav: null,
      item: {},
    }),
    methods: {
      save(item) {
        const input = {}
        const allowed = ['lang','slug','domain','name','title','to','tag','data','config','status','cache','start','end']

        allowed.forEach(key => {
          if(typeof item[key] !== 'undefined') {
            input[key] = item[key]
          }
        })

        for(const key of ['start', 'end']) {
          input[key] = input[key] ? input[key].replace(/T/, ' ') + ':00' : null
        }

        this.$apollo.mutate({
          mutation: gql`mutation ($id: ID!, $input: PageInput!) {
            savePage(id: $id, input: $input) {
              id
              parent_id
              domain
              slug
              lang
              name
              title
              to
              tag
              status
              cache
              start
              end
              editor
              created_at
              updated_at
              deleted_at
              has
            }
          }`,
          variables: {
            id: item.id,
            input: input
          }
        }).then(result => {
          if(!result.errors) {
            this.item = Object.assign(this.item, result.data.savePage)
          } else {
            console.log(`savePage(id: ${item.id})`, result)
          }
        }).catch(error => {
          console.log(`savePage(id: ${item.id})`, error)
        })

        this.details = false
      }
    }
  }
</script>

<template>

    <transition-group name="slide">
      <v-layout class="page-tree" key="tree">
        <PageTree
          v-model:nav="nav"
          v-model:item="item"
          @update:item="details = true"
        />
      </v-layout>
      <v-layout class="page-details" key="details" v-show="details">
        <PageDetails
          :item="item"
          @update:item="save($event)"
        />
      </v-layout>
    </transition-group>

</template>

<style>
  .page-tree, .page-details {
    position: absolute;
    min-height: 100vh;
    right: 0;
    left: 0;
    top: 0;
  }
</style>
