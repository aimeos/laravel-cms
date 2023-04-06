<script>
  import Navigation from '../components/Navigation.vue'
  import PageDetails from '../components/PageDetails.vue'

  export default {
    components: {
      Navigation,
      PageDetails
    },
    data: () => ({
      nav: null,
      aside: null
    }),
  }
</script>

<template>
    <v-layout>

      <v-app-bar :elevation="2" density="compact" image="src/assets/background.png">
        <template v-slot:prepend>
          <v-btn @click.stop="nav = !nav"><v-icon size="x-large">{{ nav ? 'mdi-close' : 'mdi-menu' }}</v-icon></v-btn>
        </template>

        <v-app-bar-title>Files</v-app-bar-title>

        <v-btn @click.stop="aside = !aside"><v-icon size="x-large">{{ aside ? 'mdi-chevron-right' : 'mdi-chevron-left' }}</v-icon></v-btn>
      </v-app-bar>

      <Navigation v-model="nav" />

    </v-layout>
</template>

<style>
  header.v-app-bar {
    background-color: #102050 !important;
    color: #fff !important;
  }

  nav.v-navigation-drawer {
    background-color: #ebf4ff;
  }

  .v-main, .v-sheet {
    background-color: #f8fbff !important;
  }

  .v-sheet {
    padding: 1rem 1%;
  }
</style>
