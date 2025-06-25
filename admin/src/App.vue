<script>
  import gql from 'graphql-tag'
  import { useMessageStore } from './stores'
  import { computed, markRaw, provide } from 'vue'

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
        compose: this.composeText
      }
    },

    setup() {
      const messages = useMessageStore()
      return { messages }
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


      composeText(prompt, context = []) {
        prompt = prompt ? prompt.trim() : (Array.isArray(context) && context.length ? 'Generate text based on context' : null)

        if(!prompt) {
          this.messages.add('Prompt is required for generating text', 'error')
          return Promise.reject(new Error('Prompt is required'))
        }

        if(!Array.isArray(context)) {
          context = [context]
        }

        context.push('only return the requested data without any additional information')

        return this.$apollo.mutate({
          mutation: gql`mutation($prompt: String!, $context: String) {
            compose(prompt: $prompt, context: $context)
          }`,
          variables: {
            prompt: prompt,
            context: context.filter(v => !!v).join(', ')
          }
        }).then(result => {
          if(result.errors) {
            throw result
          }

          return result.data?.compose?.replace(/^"(.*)"$/, '$1') || ''
        }).catch(error => {
          this.messages.add('Error generating text', 'error')
          this.$log(`App::compose(): Error generating text`, error)
        })
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
    overflow-y: auto;
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
