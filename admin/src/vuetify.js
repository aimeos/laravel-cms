import gettext from './i18n'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'
import { createVuetify } from 'vuetify'
import { ar, bg, cs, da, de, el, en, es, et, fi, fr, he, hu, id, it, ja, ko, lt, lv, no, nl, pl, pt, ro, ru, sk, sl, sv, th, tr, uk, vi, zhHans } from 'vuetify/locale'


const vuetify = createVuetify({
  components,
  directives,
  icons: {
    defaultSet: 'mdi',
  },
  locale: {
    locale: gettext.current,
    fallback: 'en',
    messages: { ar, bg, cs, da, de, el, en, es, et, fi, fr, he, hu, id, it, ja, ko, lt, lv, no, nl, pl, pt, ro, ru, sk, sl, sv, th, tr, uk, vi, zhHans }
  },
  theme: {
    themes: {
      light: {
        dark: false,
        colors: {
          primary: '#0068D0',
          background: '#F8FAFC'
        }
      },
    },
  },
})

export default vuetify