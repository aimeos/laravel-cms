<script>
  import Navigation from '../components/Navigation.vue'
  import PageDetails from '../components/PageDetails.vue'
  import bgUrl from '../assets/bg.jpg'

  export default {
    components: {
      Navigation,
      PageDetails
    },
    data: () => ({
      nav: null,
      aside: null,
      bgUrl: bgUrl,
    }),
  }
</script>

<template>
    <v-layout>

      <v-app-bar :elevation="2" density="compact" :image="bgUrl">
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
  nav.v-navigation-drawer {
    background-color: #ebf4ff;
  }

  .v-sheet {
    padding: 1rem 1%;
  }
</style>
