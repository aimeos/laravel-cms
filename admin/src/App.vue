<script>
  import { computed, markRaw, provide } from 'vue'
  import { useConfigStore, useSchemaStore } from './stores'
  import { loadErrorMessages, loadDevMessages } from "@apollo/client/dev";

  loadDevMessages()
  loadErrorMessages()

  export default {
    data() {
      return {
        viewStack: [],
      }
    },

    provide() {
      return {
        openView: this.open,
        closeView: this.close,
      }
    },

    methods: {
      open(component, props = {}) {
        if(!component) {
          console.error('Component is not defined')
          return
        }

        this.viewStack.push({
          component: markRaw(component),
          props: props || {},
        })
      },


      close() {
        this.viewStack.pop()
      },
    }
  }
</script>

<template>
  <v-app>
    <transition-group name="slide-stack">
      <v-layout key="list" class="view" style="z-index: 10">
        <router-view />
      </v-layout>

      <v-layout v-for="(view, i) in viewStack" :key="i" class="view" :style="{ zIndex: 10 + i }">
        <div class="view-scroll">
          <component :is="view.component" v-bind="view.props" />
        </div>
      </v-layout>
    </transition-group>
  </v-app>
</template>

<style>
  .view {
    background: rgb(var(--v-theme-background));
    position: absolute !important;
    inset: 0;
  }

  .view-scroll {
    width: 100%;
    overflow-y: auto; /* Only this scrolls */
    -webkit-overflow-scrolling: touch; /* Smooth scroll on mobile */
  }


  /* Slide animation */
  .slide-stack-enter-active,
  .slide-stack-leave-active {
    transition: transform 0.3s ease;
  }

  .slide-stack-enter-from {
    transform: translateX(100%);
  }

  .slide-stack-leave-to {
    transform: translateX(100%);
  }
</style>
