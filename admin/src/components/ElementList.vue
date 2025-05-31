<script>
  import gql from 'graphql-tag'
  import User from './User.vue'
  import Navigation from './Navigation.vue'
  import ElementListItems from './ElementListItems.vue'

  export default {
    components: {
      ElementListItems,
      Navigation,
      User
    },

    props: {
      'nav': {type: Boolean, default: false}
    },

    emits: ['update:nav', 'update:item']
  }
</script>

<template>
  <v-app-bar :elevation="1" density="compact">
    <template #prepend>
      <v-btn @click.stop="$emit('update:nav', !nav)">
        <v-icon size="x-large">
          {{ `mdi-${nav ? 'close' : 'menu'}` }}
        </v-icon>
      </v-btn>
    </template>

    <v-app-bar-title>Shared elements</v-app-bar-title>

    <template #append>
      <User />
    </template>
  </v-app-bar>

  <Navigation :state="nav" @update:state="$emit('update:nav', $event)" />

  <v-main class="element-list">
    <ElementListItems @update:item="$emit('update:item', $event)" />
  </v-main>
</template>

<style scoped>
</style>
