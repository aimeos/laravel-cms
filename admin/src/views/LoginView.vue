<script>
  import gql from 'graphql-tag'
  import router from '../routes';
  import { useAppStore } from '../stores'

  export default {
    setup() {
      const app = useAppStore()
      return { app }
    },
    apollo: {
      me: {
        query: gql`query{
          me {
            cmseditor
            name
          }
        }`,
        error(error) {
          console.dir(error)
          this.message = error.message
          this.failure = true
        },
        update(data) {
          this.handle(data.me)
        }
      }
    },
    data: () => ({
      creds: {
        email: '',
        password: ''
      },
      form: null,
      error: null,
      loading: false,
      login: false,
      show: false
    }),
    methods: {
      cmslogin() {
        if(!this.creds.email || !this.creds.password) {
          return false
        }

        this.error = null
        this.loading = true

        this.$apollo.mutate({
          mutation: gql`mutation ($email: String!, $password: String!) {
            cmsLogin(email: $email, password: $password) {
              cmseditor
              name
            }
          }`,
          variables: {
            email: this.creds.email,
            password: this.creds.password
          }
        }).then(r => {
          this.handle(r.data.cmsLogin || null)
        }).catch(err => {
          console.log(`cmsLogin(email: ${this.creds.email})`, err)
          this.error = err.message
        }).finally(() => {
          this.loading = false
        });
      },

      handle(result) {
        if(result) {
          if(result.name) {
            if(result.cmseditor) {
              this.app.me = result.name
              this.error = null
              router.push('/pages')
            } else {
              this.error = 'Not a CMS editor'
            }
          } else {
            this.error = error.message
          }
        } else {
          this.login = true
        }
      }
    }
  }
</script>

<template>
  <v-form class="login" :class="{show: login}" v-model="form" @submit.prevent="cmslogin()">
    <v-card :loading="loading" class="elevation-2" :class="{error: error}">
      <template v-slot:title>
        Login
      </template>

      <v-card-text>
        <v-text-field v-model="creds.email" label="E-Mail" variant="underlined" validate-on="blur" :rules="[
          v => !!v || 'Field is required',
          v => !!v.match(/.*@.*/) || 'Invalid e-mail address'
        ]" clearable autofocus>
        </v-text-field>
        <v-text-field v-model="creds.password" :type="show ? `text` : `password`"
          label="Password" variant="underlined" :rules="[
            v => !!v || 'Field is required'
          ]" clearable>
          <template v-slot:append-inner>
            <v-icon @click="show = !show">{{ show ? `mdi-eye-off` : `mdi-eye` }}</v-icon>
          </template>
        </v-text-field>
        <v-alert v-show="error" color="error" icon="mdi-alert-octagon" :text="`Error: ` + error"></v-alert>
      </v-card-text>

      <v-card-actions>
        <v-btn type="submit" variant="outlined" :disabled="form != true">Login</v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<style>
  body {
    background-image: url('src/assets/bg-full.jpg');
    background-size: cover;
  }

  #app {
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100vh;
  }

  .login .v-card {
    width: 20rem;
    padding: 1rem;
    background-color: #10446b;
    color: #fff;
    opacity: 0;
  }

  .login.show .v-card {
    opacity: 1;
    transition: opacity 0.5s;
  }

  .login .v-card-actions {
    justify-content: center;
  }

  .login .v-theme--light,
  .login .v-field--error,
  .login .v-field--error:not(.v-field--disabled) .v-field__clearable > .v-icon {
    --v-theme-error: 255,167,38;
  }

  .login .error {
    animation: shake 0.82s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
    transform: translate3d(0, 0, 0);
  }

  @keyframes shake {
    10%, 90% {
      transform: translate3d(-1px, 0, 0);
    }

    20%, 80% {
      transform: translate3d(2px, 0, 0);
    }

    30%, 50%, 70% {
      transform: translate3d(-4px, 0, 0);
    }

    40%, 60% {
      transform: translate3d(4px, 0, 0);
    }
  }
</style>
