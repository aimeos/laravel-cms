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
    },

    setup() {
      const config = useConfigStore()

      config.data = {
        themes: {
          'default': {
            types: {
              'default': {
                sections: [
                  'main',
                  'header',
                  'sidebar',
                  'footer',
                ]
              },
              blog: {
              }
            }
          },
          fashion: {
          },
        }
      }


      const schemas = useSchemaStore()

      schemas.content = {
        'heading': {
          type: 'heading',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3,4H5V10H9V4H11V18H9V12H5V18H3V4M13,8H15.31L15.63,5H17.63L17.31,8H19.31L19.63,5H21.63L21.31,8H23V10H21.1L20.9,12H23V14H20.69L20.37,17H18.37L18.69,14H16.69L16.37,17H14.37L14.69,14H13V12H14.9L15.1,10H13V8M17.1,10L16.9,12H18.9L19.1,10H17.1Z" /></svg>',
          fields: {
            'level': {type: 'select', required: true, options: [
              {value: '1', label: 'H1'},
              {value: '2', label: 'H2'},
              {value: '3', label: 'H3'},
              {value: '4', label: 'H4'},
              {value: '5', label: 'H5'},
              {value: '6', label: 'H6'}
            ]},
            'text': {type: 'string', min: 1, max: 255},
          }
        },
        'paragraph': {
          type: 'paragraph',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21,6V8H3V6H21M3,18H12V16H3V18M3,13H21V11H3V13Z" /></svg>',
          fields: {
            'text': {type: 'text', min: 1},
          }
        },
        'image': {
          type: 'image',
          group: 'basic',
          label: 'Image',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M6.002 5.5a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0"/><path d="M2.002 1a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V3a2 2 0 0 0-2-2zm12 1a1 1 0 0 1 1 1v6.5l-3.777-1.947a.5.5 0 0 0-.577.093l-3.71 3.71-2.66-1.772a.5.5 0 0 0-.63.062L1.002 12V3a1 1 0 0 1 1-1z"/></svg>',
          fields: {
            'image': {type: 'image'},
            'main': {type: 'switch', label: 'Main image', default: false},
          }
        },
        'image-text': {
          type: 'image-text',
          group: 'basic',
          label: 'Text with image',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M7 4.25a.75.75 0 1 1-1.5 0 .75.75 0 0 1 1.5 0m-.861 1.542 1.33.886 1.854-1.855a.25.25 0 0 1 .289-.047l1.888.974V7.5a.5.5 0 0 1-.5.5H5a.5.5 0 0 1-.5-.5V7s1.54-1.274 1.639-1.208M5 9a.5.5 0 0 0 0 1h6a.5.5 0 0 0 0-1zm0 2a.5.5 0 0 0 0 1h3a.5.5 0 0 0 0-1z"/><path d="M2 2a2 2 0 0 1 2-2h8a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2zm10-1H4a1 1 0 0 0-1 1v12a1 1 0 0 0 1 1h8a1 1 0 0 0 1-1V2a1 1 0 0 0-1-1"/></svg>',
          fields: {
            'image': {type: 'image'},
            'text': {type: 'text', min: 1},
          }
        },
        'cards': {
          type: 'cards',
          group: 'content',
          label: 'List of cards',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>',
          fields: {
            'cards': {type: 'items', item: {
              'name': {type: 'string', min: 1},
              'text': {type: 'text'},
              'image': {type: 'image'},
            }},
          }
        },
        'code': {
          type: 'code',
          group: 'basic',
          label: 'Code block',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M2.114 8.063V7.9c1.005-.102 1.497-.615 1.497-1.6V4.503c0-1.094.39-1.538 1.354-1.538h.273V2h-.376C3.25 2 2.49 2.759 2.49 4.352v1.524c0 1.094-.376 1.456-1.49 1.456v1.299c1.114 0 1.49.362 1.49 1.456v1.524c0 1.593.759 2.352 2.372 2.352h.376v-.964h-.273c-.964 0-1.354-.444-1.354-1.538V9.663c0-.984-.492-1.497-1.497-1.6M13.886 7.9v.163c-1.005.103-1.497.616-1.497 1.6v1.798c0 1.094-.39 1.538-1.354 1.538h-.273v.964h.376c1.613 0 2.372-.759 2.372-2.352v-1.524c0-1.094.376-1.456 1.49-1.456V7.332c-1.114 0-1.49-.362-1.49-1.456V4.352C13.51 2.759 12.75 2 11.138 2h-.376v.964h.273c.964 0 1.354.444 1.354 1.538V6.3c0 .984.492 1.497 1.497 1.6"/></svg>',
          fields: {
            'lang': {type: 'combobox', label: 'Language', options: [
              {value: 'css', label: 'CSS'},
              {value: 'graphql', label: 'GraphQL'},
              {value: 'html', label: 'HTML'},
              {value: 'java', label: 'Java'},
              {value: 'javascript', label: 'JavaScript'},
              {value: 'json', label: 'JSON'},
              {value: 'markdown', label: 'Markdown'},
              {value: 'php', label: 'PHP'},
              {value: 'python', label: 'Python'},
              {value: 'ruby', label: 'Ruby'},
              {value: 'sql', label: 'SQL'},
              {value: 'typescript', label: 'TypeScript'},
              {value: 'xml', label: 'XML'},
            ]},
            'text': {type: 'plaintext', min: 1},
          }
        },
        'table': {
          type: 'table',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M0 2a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2zm15 2h-4v3h4zm0 4h-4v3h4zm0 4h-4v3h3a1 1 0 0 0 1-1zm-5 3v-3H6v3zm-5 0v-3H1v2a1 1 0 0 0 1 1zm-4-4h4V8H1zm0-4h4V4H1zm5-3v3h4V4zm4 4H6v3h4z"/></svg>',
          fields: {
            'title': {type: 'string'},
            'header': {type: 'select', options: [
              {value: '', label: 'None'},
              {value: 'row', label: 'First row'},
              {value: 'col', label: 'First column'},
              {value: 'row+col', label: 'First row and column'},
            ]},
            'content': {type: 'table'},
          }
        },
        'html': {
          type: 'html',
          group: 'basic',
          label: 'HTML code',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 16 16"><path d="M10.478 1.647a.5.5 0 1 0-.956-.294l-4 13a.5.5 0 0 0 .956.294zM4.854 4.146a.5.5 0 0 1 0 .708L1.707 8l3.147 3.146a.5.5 0 0 1-.708.708l-3.5-3.5a.5.5 0 0 1 0-.708l3.5-3.5a.5.5 0 0 1 .708 0m6.292 0a.5.5 0 0 0 0 .708L14.293 8l-3.147 3.146a.5.5 0 0 0 .708.708l3.5-3.5a.5.5 0 0 0 0-.708l-3.5-3.5a.5.5 0 0 0-.708 0"/></svg>',
          fields: {
            'html': {type: 'html', min: 1},
          }
        },
        'article': {
          type: 'article',
          group: 'content',
          label: 'Blog article',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>',
          fields: {
            'title': {type: 'string', min: 1, max: 255},
            'cover': {type: 'image'},
            'intro': {type: 'text', min: 1},
          }
        },

        'all': {
          type: 'all',
          label: 'All content',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>',
          fields: {
            'autocomplete': {
              type: 'autocomplete',
              multiple: true,
              placeholder: 'Search for products',
              options: [
              ],
              endpoint: {
                url: 'https://dummyjson.com/products/search?q=_term_',
                type: 'REST',
              },
              'list-key': 'products',
              'item-value': 'id',
              'item-title': 'title',
            },
            'checkbox': {type: 'checkbox'},
            'color': {type: 'color'},
            'combobox': {
              type: 'combobox',
              multiple: true,
              options: ['Test 1', 'Test 2'],
              endpoint: {
                url: '/graphql',
                type: 'GQL',
                query: `query {
                  pages(filter: {any: _term_}) {
                    data {
                      slug
                    }
                  }
                }`
              },
              'list-key': 'pages/data',
              'item-value': 'slug',
            },
            'date': {type: 'date'},
            'html': {type: 'html'},
            'file': {type: 'file'},
            'audio': {type: 'audio'},
            'video': {type: 'video'},
            'image': {type: 'image'},
            'images': {type: 'images'},
            'items': {type: 'items', item: {
              'name': {type: 'string'},
            }, max: 3},
            'markdown': {type: 'markdown'},
            'number': {type: 'number'},
            'plaintext': {type: 'plaintext'},
            'radio': {type: 'radio', options: [
              {value: 't1', label: 'Test 1'},
              {value: 't2', label: 'Test 2'},
            ]},
            'range': {type: 'range'},
            'select': {type: 'select', options: [
              {value: 't1', label: 'Test 1'},
              {value: 't2', label: 'Test 2'},
            ]},
            'slider': {type: 'slider'},
            'string': {type: 'string'},
            'switch': {type: 'switch'},
            'table': {type: 'table'},
            'text': {type: 'text'},
            'url': {type: 'url', required: true},
          }
        }
      }

      schemas.meta = {
        'meta': {
          type: 'meta',
          group: 'basic',
          label: 'Meta tags for search engines',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M21.35,11.1H12.18V13.83H18.69C18.36,17.64 15.19,19.27 12.19,19.27C8.36,19.27 5,16.25 5,12C5,7.9 8.2,4.73 12.2,4.73C15.29,4.73 17.1,6.7 17.1,6.7L19,4.72C19,4.72 16.56,2 12.1,2C6.42,2 2.03,6.8 2.03,12C2.03,17.05 6.16,22 12.25,22C17.6,22 21.5,18.33 21.5,12.91C21.5,11.76 21.35,11.1 21.35,11.1V11.1Z" /></svg>',
          fields: {
            'description': {type: 'string', min: 1, max: 180},
            'keywords': {type: 'string', max: 255},
          }
        },
        'social': {
          type: 'social',
          group: 'basic',
          label: 'Social media related data',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z" /></svg>',
          fields: {
            'title': {type: 'string', min: 1, max: 255},
            'image': {type: 'image', required: true},
          },
        },
        'canonical': {
          type: 'canonical',
          group: 'basic',
          label: 'Canonical URL',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5,6.41L6.41,5L17,15.59V9H19V19H9V17H15.59L5,6.41Z" /></svg>',
          fields: {
            'url': {type: 'url', required: true},
          }
        }
      }

      schemas.config = {
        'test': {
          type: 'test',
          label: 'Test string+color',
          fields: {
            'test/key': {type: 'url', label: 'Test string config', min: 1},
            'test/color': {type: 'color', label: 'Test color selector'}
          }
        },
        'test2': {
          type: 'test2',
          label: 'Test config',
          fields: {
            'test2/key': {type: 'string', label: 'Second string value', min: 1},
            'file': {type: 'file', label: 'File upload'},
          }
        },
      }

      return { config, schemas }
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
