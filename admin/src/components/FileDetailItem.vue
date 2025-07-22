<script>
  import gql from 'graphql-tag'
  import Cropper from 'cropperjs'
  import 'cropperjs/dist/cropper.css'
  import { url2audio } from '../utils'
  import { useAppStore, useAuthStore, useLanguageStore, useMessageStore, useSideStore } from '../stores'


  export default {
    props: {
      'item': {type: Object, required: true}
    },

    emits: ['update:item', 'update:file', 'error'],

    inject: ['compose', 'locales', 'translate', 'txlocales'],

    data() {
      return {
        transcribing: false,
        translating: false,
        composing: false,
        tabtrans: null,
        tabdesc: null,
        cropper: null,
        scaleX: 1,
        scaleY: 1,
      }
    },

    setup() {
      const languages = useLanguageStore()
      const messages = useMessageStore()
      const side = useSideStore()
      const auth = useAuthStore()
      const app = useAppStore()

      return { app, auth, languages, messages, side }
    },

    mounted() {
      if(!this.readonly && this.item.mime?.startsWith('image/')) {
        this.cropper = new Cropper(this.$refs.image, {
          aspectRatio: NaN,
          background: true,
          responsive: true,
          dragMode: 'move',
          movable: false,
          autoCrop: false,
          zoomable: true,
          zoomOnWheel: false,
          zoomOnTouch: false,
          checkCrossOrigin: false,
          checkOrientation: false,
          viewMode: 1,
        })
      }
    },

    beforeUnmount() {
      if(this.cropper) {
        this.cropper.destroy()
      }
    },

    computed: {
      desclangs() {
        return this.languages.available.concat(Object.keys(this.item.description || {})).filter((v, idx, self) => {
          return self.indexOf(v) === idx
        })
      },


      readonly() {
        return !this.auth.can('file:save')
      }
    },

    methods: {
      composeText() {
        const lang = this.desclangs.shift() || this.item.lang || 'en'
        const prompt = `Summarize the content of the file in a few words in plain text format for a title tag in the language with the ISO code "${lang}":`

        this.composing = true

        this.compose(prompt, null, [this.item.id]).then(result => {
          this.item.description[lang] = result
        }).finally(() => {
          this.composing = false
        })
      },


      download() {
        this.cropper.getCroppedCanvas().toBlob(blob => {
          const url = URL.createObjectURL(blob)
          const link = document.createElement('a')

          link.href = url
          link.download = this.item.name || 'download'
          link.click()

          URL.revokeObjectURL(url)
        })
      },


      flipX() {
        this.scaleX = -this.scaleX
        this.cropper.scaleX(this.scaleX)
        this.save()
      },


      flipY() {
        this.scaleY = -this.scaleY
        this.cropper.scaleY(this.scaleY)
        this.save()
      },


      reset() {
        this.$emit('update:file', null)
        this.cropper.reset()
        this.cropper.clear()
        this.scaleX = 1
        this.scaleY = 1
      },


      rotate(deg) {
        this.cropper.rotate(deg)
        this.save()

        this.$nextTick(() => {
          const container = this.cropper.getContainerData()
          const image = this.cropper.getImageData()
          let scaleX, scaleY

          if(Math.abs(Math.abs(image.rotate) - 180) === 90) {
            scaleX = container.width / image.naturalHeight
            scaleY = container.height / image.naturalWidth
          } else {
            scaleX = container.width / image.naturalWidth
            scaleY = container.height / image.naturalHeight
          }

          this.cropper.zoomTo(Math.min(scaleX, scaleY))
        });
      },


      save() {
        this.cropper.getCroppedCanvas().toBlob(blob => {
          this.$emit('update:file', blob)
        })
      },


      update(what, value) {
        this.item[what] = value
        this.$emit('update:item', this.item)
      },


      transcribe() {
        if(!this.item.mime?.startsWith('audio/') && !this.item.mime?.startsWith('video/')) {
          return this.messages.add(this.$gettext('Transcription is only available for audio and video files'), 'error')
        }

        this.transcribing = true

        url2audio(this.url(this.item.path, true)).then(blob => {
          return this.$apollo.mutate({
            mutation: gql`mutation($file: Upload!) {
              transcribe(file: $file)
            }`,
            variables: {
              file: new File([blob], 'audio.mp3', { type: 'audio/mpeg' })
            },
            context: {
              hasUpload: true,
            }
          })
        }).then(result => {
          if(result.errors) {
            throw result
          }

          if(!this.item.transcription) {
            this.item.transcription = {}
          }

          const lang = this.desclangs.shift() || this.item.lang || 'en'
          this.item.transcription[lang] = result.data?.transcribe || ''

          this.$emit('update:item', this.item)
        }).catch(error => {
          this.messages.add(this.$gettext('Error transcribing file'), 'error')
          this.$log(`FileDetailItem::transcribe(): Error transcribing from media URL`, error)
        }).finally(() => {
          this.transcribing = false
        })
      },


      translateText(map) {
        if(!map || typeof map !== 'object') {
          this.$log(`FileDetailItem::translateText(): Invalid map object`, map)
          return
        }

        const promises = []
        const [lang, text] = Object.entries(map || {}).find(([lang, text]) => {
          return text ? true : false
        })

        this.translating = true

        this.txlocales(lang).map(lang => lang.code).forEach(lang => {
          promises.push(this.translate(text, lang).then(result => {
            if(result[0]) {
              map[lang] = result[0]
            }
          }).catch(error => {
            this.$log(`FileDetailItem::translateText(): Error translating text`, error)
          }))
        })

        Promise.all(promises).then(() => {
          this.translating = false
        })
      },


      url(path, proxy = false) {
        if(proxy && path.startsWith('http')) {
          return this.app.urlproxy.replace(/:url/, encodeURIComponent(path))
        }
        if(path.startsWith('blob:') || path.startsWith('http')) {
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
            :label="$gettext('Name')"
            counter="255"
            maxlength="255"
          ></v-text-field>
        </v-col>
        <v-col cols="12" md="6">
          <v-select ref="lang"
            :items="locales(true)"
            :readonly="readonly"
            :modelValue="item.lang"
            @update:modelValue="update('lang', $event)"
            variant="underlined"
            :label="$gettext('Language')"
          ></v-select>
        </v-col>
      </v-row>
      <v-row>
        <v-col v-if="item" cols="12" class="preview">
          <div v-if="item.mime?.startsWith('image/')" ref="editorContainer" class="editor-container">
            <img ref="image" :src="url(item.path, true)" class="element" crossorigin="anonymous" />

            <div v-if="!readonly" class="floating-toolbar">
              <div class="toolbar-group">
                <v-btn icon="mdi-rotate-left" class="no-rtl" @click="rotate(-90)" :title="$gettext('Rotate counter-clockwise')" />
                <v-btn icon="mdi-rotate-right" class="no-rtl" @click="rotate(90)" :title="$gettext('Rotate clockwise')" />
              </div>
              <div class="toolbar-group">
                <v-btn icon="mdi-flip-horizontal" class="no-rtl" @click="flipX" :title="$gettext('Flip horizontally')" />
                <v-btn icon="mdi-flip-vertical" class="no-rtl" @click="flipY" :title="$gettext('Flip vertically')" />
              </div>
              <div class="toolbar-group">
                <v-btn icon="mdi-history" class="no-rtl" @click="reset()" :title="$gettext('Reset')" />
                <v-btn icon="mdi-download" class="no-rtl" @click="download()" :title="$gettext('Download')" />
              </div>
            </div>
          </div>
          <video v-else-if="item.mime?.startsWith('video/')"
            :src="url(item.path)"
            crossorigin="anonymous"
            preload="metadata"
            class="element"
            controls
          ></video>
          <audio v-else-if="item.mime?.startsWith('audio/')"
            :src="url(item.path)"
            crossorigin="anonymous"
            preload="metadata"
            class="element"
            controls
          ></audio>
          <svg v-else xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-file-earmark-binary" viewBox="0 0 16 16">
            <path d="M7.05 11.885c0 1.415-.548 2.206-1.524 2.206C4.548 14.09 4 13.3 4 11.885c0-1.412.548-2.203 1.526-2.203.976 0 1.524.79 1.524 2.203m-1.524-1.612c-.542 0-.832.563-.832 1.612q0 .133.006.252l1.559-1.143c-.126-.474-.375-.72-.733-.72zm-.732 2.508c.126.472.372.718.732.718.54 0 .83-.563.83-1.614q0-.129-.006-.25zm6.061.624V14h-3v-.595h1.181V10.5h-.05l-1.136.747v-.688l1.19-.786h.69v3.633z"/>
            <path d="M14 14V4.5L9.5 0H4a2 2 0 0 0-2 2v12a2 2 0 0 0 2 2h8a2 2 0 0 0 2-2M9.5 3A1.5 1.5 0 0 0 11 4.5h2V14a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V2a1 1 0 0 1 1-1h5.5z"/>
          </svg>
        </v-col>
      </v-row>
      <v-row>
        <v-col cols="12" class="description">
          <v-label>
            {{ $gettext('Descriptions') }}
            <div v-if="!readonly" class="actions">
              <v-btn v-if="Object.values(item.description || {}).find(v => !!v)"
                :loading="translating"
                  icon="mdi-translate"
                  variant="flat"
                  @click="translateText(item.description)" />
              <v-btn
                :loading="composing"
                icon="mdi-creation"
                variant="flat"
                @click="composeText()"
              />
            </div>
          </v-label>

          <v-tabs v-model="tabdesc">
            <v-tab v-for="entry in locales()" :value="entry.value">{{ entry.title }}</v-tab>
          </v-tabs>
          <v-window v-model="tabdesc">
            <v-window-item v-for="entry in locales()" :value="entry.value">
              <v-textarea ref="description"
                :readonly="readonly"
                :modelValue="item.description?.[entry.value] || ''"
                @update:modelValue="item.description[entry.value] = $event; $emit('update:item', item)"
                :label="$gettext('Description (%{lang})', {lang: entry.value})"
                variant="underlined"
                counter="500"
                rows="2"
                auto-grow
                clearable
              ></v-textarea>
            </v-window-item>
          </v-window>
        </v-col>
      </v-row>
      <v-row v-if="item.mime?.startsWith('audio/') || item.mime?.startsWith('video/')">
        <v-col cols="12" class="transcription">
          <v-label>
            {{ $gettext('Transcriptions') }}
            <div v-if="!readonly" class="actions">
              <v-btn v-if="Object.values(item.transcription || {}).find(v => !!v)"
                :loading="translating"
                  icon="mdi-translate"
                  variant="flat"
                  @click="translateText(item.transcription)" />
              <v-btn
                :loading="transcribing"
                icon="mdi-creation"
                variant="flat"
                @click="transcribe()"
              />
            </div>
          </v-label>

          <v-tabs v-model="tabtrans">
            <v-tab v-for="entry in locales()" :value="entry.value">{{ entry.title }}</v-tab>
          </v-tabs>
          <v-window v-model="tabtrans">
            <v-window-item v-for="entry in locales()" :value="entry.value">
              <v-textarea ref="transcription"
                :readonly="readonly"
                :modelValue="item.transcription?.[entry.value] || ''"
                @update:modelValue="item.transcription[entry.value] = $event; $emit('update:item', item)"
                :label="$gettext('Transcription (%{lang})', {lang: entry.value})"
                variant="underlined"
                rows="20"
                auto-grow
                clearable
              ></v-textarea>
            </v-window-item>
          </v-window>
        </v-col>
      </v-row>
    </v-sheet>
  </v-container>
</template>

<style scoped>
  :deep(.cropper-bg) {
    background-repeat: repeat;
  }

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

  .editor-container {
    width: 100%;
    max-height: 600px;
    text-align: center;
    overflow: hidden;
    position: relative;
  }

  .editor-container img {
    max-width: none !important;
    max-height: none !important;
    transform-origin: center center;
  }

  .floating-toolbar {
    background-color: rgba(33, 33, 33, 0.5);
    transform: translateX(-50%);
    border-radius: 10px;
    position: absolute;
    z-index: 1000;
    padding: 10px;
    bottom: 30px;
    left: 50%;
    gap: 8px;
    display: flex;
    flex-wrap: nowrap;
    justify-content: center;
    width: max-content;
  }

  .toolbar-group {
    display: flex;
    gap: 8px;
  }

  .description .v-label,
  .transcription .v-label {
    display: flex;
    align-items: center;
    justify-content: space-between;
    text-transform: capitalize;
    font-weight: bold;
    margin-bottom: 4px;
    margin-top: 40px;
  }

  .description .v-textarea,
  .transcription .v-textarea {
    margin-top: 16px;
  }

  @media (max-width: 480px) {
    .floating-toolbar {
      width: auto;
    }

    .toolbar-group {
      flex-direction: column;
      justify-content: center;
      gap: 4px;
    }
  }
</style>