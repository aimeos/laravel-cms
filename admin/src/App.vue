<script>
  import gql from 'graphql-tag'
  import { computed, markRaw, provide } from 'vue'
  import { useLanguageStore, useMessageStore } from './stores'

  export default {
    data() {
      return {
        viewStack: [],
      }
    },

    provide() {
      return {
        debounce: this.debouncer,
        openView: this.open,
        closeView: this.close,
        compose: this.composeText,
        translate: this.translateText,
        txlanguages: this.txlangs,
      }
    },

    setup() {
      const languages = useLanguageStore()
      const messages = useMessageStore()
      return { languages, messages }
    },

    methods: {
      debouncer(func, delay) {
        let timer

        return function(...args) {
          return new Promise((resolve, reject) => {
            const context = this

            clearTimeout(timer)
            timer = setTimeout(() => {
              try {
                resolve(func.apply(context, args))
              } catch (error) {
                reject(error)
              }
            }, delay)
          })
        }
      },


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
          this.$log(`App::composeText(): Error generating text`, error)
        })
      },


      translateText(texts, to, from = null) {
        if(!Array.isArray(texts)) {
          texts = [texts].filter(v => !!v)
        }

        if(!texts.length) {
          return Promise.resolve([])
        }

        if(!to) {
          return Promise.reject(new Error('Target language is required'))
        }

        return this.$apollo.mutate({
          mutation: gql`mutation($texts: [String!]!, $to: String!, $from: String) {
            translate(texts: $texts, to: $to, from: $from)
          }`,
          variables: {
            texts: texts,
            to: to.toUpperCase(),
            from: from?.toUpperCase(),
          }
        }).then(result => {
          if(result.errors) {
            throw result
          }

          return result.data?.translate || []
        }).catch(error => {
          this.messages.add('Error translating texts', 'error')
          this.$log(`App::translateText(): Error translating texts`, error)
        })
      },


      txlangs(current = null) {
        const list = []
        const supported = [
          'ar', 'bg', 'cs', 'da', 'de', 'el', 'en', 'en-GB', 'en_US', 'es', 'et', 'fi', 'fr',
          'he', 'hu', 'id', 'it', 'ja', 'ko', 'lt', 'lv', 'nb', 'nl', 'pl', 'pt', 'pt-BR',
          'ro', 'ru', 'sk', 'sl', 'sv', 'th', 'tr', 'uk', 'vi', 'zh', 'zh-HANS', 'zh-HANT'
        ]

        Object.entries(this.languages.available).forEach(pair => {
          if(supported.includes(pair[0]) && pair[0] !== current) {
            list.push({code: pair[0], name: pair[1]})
          }
        })

        return list
      }
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
    overflow-y: scroll;
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
