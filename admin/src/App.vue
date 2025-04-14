<script>
  import { useElementStore } from './stores'

  export default {
    setup() {
      const elements = useElementStore()

      elements.content = {
        'heading': {
          type: 'heading',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M3,4H5V10H9V4H11V18H9V12H5V18H3V4M13,8H15.31L15.63,5H17.63L17.31,8H19.31L19.63,5H21.63L21.31,8H23V10H21.1L20.9,12H23V14H20.69L20.37,17H18.37L18.69,14H16.69L16.37,17H14.37L14.69,14H13V12H14.9L15.1,10H13V8M17.1,10L16.9,12H18.9L19.1,10H17.1Z" /></svg>',
          fields: {
            'level': {type: 'select', required: true, options: [
              {value: 1, label: 'H1'},
              {value: 2, label: 'H2'},
              {value: 3, label: 'H3'},
              {value: 4, label: 'H4'},
              {value: 5, label: 'H5'},
              {value: 6, label: 'H6'}
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
        'article': {
          type: 'article',
          group: 'content',
          label: 'Blog article',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>',
          fields: {
            'title': {type: 'string', min: 1, max: 255},
            'plain': {type: 'plaintext', min: 1},
            'intro': {type: 'text', min: 1},
            'text': {type: 'markdown', min: 1},
            'cover': {type: 'image'},
            'gallery': {type: 'images'},
          }
        },
        'services': {
          type: 'services',
          group: 'content',
          label: 'List of services',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M10 21H5C3.89 21 3 20.11 3 19V5C3 3.89 3.89 3 5 3H19C20.11 3 21 3.89 21 5V10.33C20.7 10.21 20.37 10.14 20.04 10.14C19.67 10.14 19.32 10.22 19 10.37V5H5V19H10.11L10 19.11V21M7 9H17V7H7V9M7 17H12.11L14 15.12V15H7V17M7 13H16.12L17 12.12V11H7V13M21.7 13.58L20.42 12.3C20.21 12.09 19.86 12.09 19.65 12.3L18.65 13.3L20.7 15.35L21.7 14.35C21.91 14.14 21.91 13.79 21.7 13.58M12 22H14.06L20.11 15.93L18.06 13.88L12 19.94V22Z" /></svg>',
          fields: {
            'cards': {type: 'items', item: {
              'name': {type: 'string', min: 1},
              'text': {type: 'text', min: 1},
              'image': {type: 'image'},
            }},
          }
        },
      }

      elements.meta = {
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
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z" /></svg>',
          fields: {
            'title': {type: 'string'},
            'image': {type: 'image'},
          },
        },
        'canonical': {
          type: 'canonical',
          group: 'basic',
          icon: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"><path d="M5,6.41L6.41,5L17,15.59V9H19V19H9V17H15.59L5,6.41Z" /></svg>',
          fields: {
            'url': {type: 'url', required: true},
          }
        }
      }

      elements.config = {
        'test': {
          type: 'test',
          label: 'Test string+color',
          fields: {
            'test/key': {type: 'string', label: 'Test string config'},
            'test/color': {type: 'color', label: 'Test color selector'}
          }
        },
        'test2': {
          type: 'test2',
          fields: {
            'test2/key': {type: 'string', label: 'Second string value'}
          }
        },
      }

      return { elements }
    }
  }
</script>

<template>
  <router-view></router-view>
</template>

<style>
</style>
