<script>
  import router from '../routes'
  import { useGettext } from "vue3-gettext"
  import { useAuthStore, useLanguageStore, useMessageStore } from '../stores'

  export default {
    data: () => ({
      user: null
    }),

    setup() {
      const languages = useLanguageStore()
      const messages = useMessageStore()
      const auth = useAuthStore()
      const i18n = useGettext()

      return { auth, i18n, languages, messages }
    },

    created() {
      this.auth.user().then(user => {
        this.user = user
      }).catch(err => {
        this.messages.add({message: this.$gettext('Failed to load user'), color: 'error'})
      })
    },

    methods: {
      logout() {
        this.auth.logout().then(() => {
          this.user = null
          router.replace('/')
        }).catch(err => {
          this.messages.add(this.$gettext('Logout failed'))
        })
      },


      change(code) {
        import(`../../i18n/${code}.json`).then(translations => {
          this.i18n.translations = translations.default || translations
          this.i18n.current = code
          this.$vuetify.locale.current = code
        })
      }
    }
  }
</script>

<template>
  <v-menu>
    <template #activator="{ props }">
        <v-btn icon="mdi-web" v-bind="props" class="icon"></v-btn>
    </template>
    <v-list>
        <v-list-item v-for="(_, code) in i18n.available" :key="code">
          <v-btn variant="text" @click="change(code)">{{ languages.translate(code) }} ({{ code }})</v-btn>
        </v-list-item>
    </v-list>
  </v-menu>

  <v-menu v-if="user">
    <template #activator="{ props }">
        <v-btn icon="mdi-account-circle-outline" v-bind="props" class="icon"></v-btn>
    </template>
    <v-list>
        <v-list-item v-if="user?.name">
          {{ user.name }}
        </v-list-item>
        <v-list-item>
          <v-btn class="menu" variant="text" @click="logout()">
            {{ $gettext('Logout') }}
          </v-btn>
        </v-list-item>
    </v-list>
  </v-menu>
</template>

<style scoped>
</style>
