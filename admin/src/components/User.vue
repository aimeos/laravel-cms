<script>
  import router from '../routes'
  import { useAuthStore, useMessageStore } from '../stores'

  export default {
    data: () => ({
      user: null
    }),

    setup() {
      const messages = useMessageStore()
      const auth = useAuthStore()

      return { auth, messages }
    },

    created() {
      this.auth.user().then(user => {
        this.user = user
      }).catch(err => {
        this.messages.add({message: 'Failed to load user', color: 'error'})
      })
    },

    methods: {
      logout() {
        this.auth.logout().then(() => {
          this.user = null
          router.replace('/')
        }).catch(err => {
          this.messages.add('Logout failed')
        })
      }
    }
  }
</script>

<template>
  <v-menu v-if="user">
    <template #activator="{ props }">
        <v-icon icon="mdi-account-circle-outline" v-bind="props" class="icon"></v-icon>
    </template>
    <v-list>
        <v-list-item v-if="user?.name">
          {{ user.name }}
        </v-list-item>
        <v-list-item>
          <v-btn class="menu" variant="text" @click="logout()">Logout</v-btn>
        </v-list-item>
    </v-list>
  </v-menu>
</template>

<style scoped>
</style>
