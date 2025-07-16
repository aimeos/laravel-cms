import { createGettext } from "vue3-gettext";

const gettext = createGettext({
  defaultLanguage: "en",
  translations: {},
  silent: true,
});

import(`../i18n/LINGUAS?raw`).then(content => {
  const langs = content.default.split(' ')
  const userLang = navigator.language.toLowerCase().replace('_', '-')
  const baseLang = userLang.slice(0, 2)
  const locale = langs.includes(userLang) ? userLang : langs.includes(baseLang) ? baseLang : 'en'

  gettext.available = Object.fromEntries(langs.map(value => [value, value]))

  import(`../i18n/${locale}.json`).then(translations => {
    gettext.translations = translations.default || translations
    gettext.current = locale
  })
})

export default gettext
