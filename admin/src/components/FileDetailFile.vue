<script>
  import { useAppStore, useAuthStore, useLanguageStore, useSideStore } from '../stores'


  export default {
    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item', 'error'],

    setup() {
      const languages = useLanguageStore()
      const side = useSideStore()
      const auth = useAuthStore()
      const app = useAppStore()

      return { app, auth, languages, side }
    },

    computed: {
      langs() {
        return Object.keys(this.languages.available || {}).concat(Object.keys(this.item.description || {})).filter((v, idx, self) => {
          return self.indexOf(v) === idx
        })
      },


      readonly() {
        return !this.auth.can('file:save')
      }
    },

    methods: {
      update(what, value) {
        this.item[what] = value
        this.$emit('update:item', this.item)
      },


      url(path) {
        if(path.startsWith('http') || path.startsWith('blob:')) {
          return path
        }
        return this.app.urlfile.replace(/\/+$/g, '') + '/' + path
      }
    }
  }
</script>

<template>
  <v-container>
    <v-sheet>
      <v-row>
        <v-col cols="12" md="6">
          <v-text-field ref="name"
            :readonly="readonly"
            :modelValue="item.name"
            @update:modelValue="update('name', $event)"
            variant="underlined"
            label="Name"
            counter="255"
            maxlength="255"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="6">
          <v-text-field ref="tag"
            :readonly="readonly"
            :modelValue="item.tag"
            @update:modelValue="update('tag', $event)"
            variant="underlined"
            label="Tag"
            counter="30"
            maxlength="30"
          ></v-text-field>
        </v-col>
      </v-row>
      <v-row>
        <v-col v-for="lang in langs" cols="12" class="desc">
          <v-textarea ref="description"
            :readonly="readonly"
            :modelValue="item.description?.[lang] || ''"
            @update:modelValue="item.description[lang] = $event; $emit('update:item', item)"
            :placeholder="`Description in ${lang}`"
            :label="`Description (${lang})`"
            variant="underlined"
            counter="500"
            rows="2"
            auto-grow
            clearable
          ></v-textarea>
        </v-col>
      </v-row>
      <v-row>
        <v-col v-if="item" cols="12" class="preview">
          <v-img v-if="item.mime?.startsWith('image/')"
            :src="url(item.path)"
            class="element"
            contain
          ></v-img>
          <video v-else-if="item.mime?.startsWith('video/')"
            preload="metadata"
            crossorigin="anonymous"
            :src="url(item.path)"
            class="element"
            controls
          ></video>
          <audio v-else-if="item.mime?.startsWith('audio/')"
            preload="metadata"
            :src="url(item.path)"
            class="element"
            controls
          ></audio>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-binary" viewBox="0 0 16 16">
            <path d="M7.05 11.885c0 1.415-.548 2.206-1.524 2.206C4.548 14.09 4 13.3 4 11.885c0-1.412.548-2.203 1.526-2.203.976 0 1.524.79 1.524 2.203m-1.524-1.612c-.542 0-.832.563-.832 1.612q0 .133.006.252l1.559-1.143c-.126-.474-.375-.72-.733-.72zm-.732 2.508c.126.472.372.718.732.718.54 0 .83-.563.83-1.614q0-.129-.006-.25zm6.061.624V14h-3v-.595h1.181V10.5h-.05l-1.136.747v-.688l1.19-.786h.69v3.633z"/>
            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
          </svg>
        </v-col>
      </v-row>
    </v-sheet>
  </v-container>
</template>

<style scoped>
  .preview {
    display: flex;
    justify-content: center;
    max-height: 600px;
  }

  .preview .element {
    max-width: 100%;
    max-height: 100%;
  }

  .preview svg {
    width: 5rem;
    height: 5rem;
  }
</style>