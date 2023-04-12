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
          console.log(error)
          this.netfail = true
          this.login = true
        },
        update(data) {
          if(data.me && data.me.name && data.me.cmseditor) {
            this.app.me = data.me.name
            router.push('/pages')
          } else {
            console.log(data)
            this.failure = true
            this.login = true
          }
        }
      }
    },
    data: () => ({
      creds: {
        email: '',
        password: ''
      },
      form: null,
      netfail: false,
      failure: false,
      loading: false,
      login: false,
      show: false
    }),
    methods: {
      cmslogin() {
        if(!this.creds.email || !this.creds.password) {
          return false
        }

        this.loading = true
        this.failure = false

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
        }).then((r) => {
          if(r.data.cmsLogin && r.data.cmsLogin.name && r.data.cmsLogin.cmseditor) {
            this.app.me = r.data.cmsLogin.name
            router.push('/pages')
          } else {
            this.failure = true
          }
        }).catch((error) => {
          console.log('error', error)
          this.netfail = true
        }).finally(() => {
          this.loading = false
        });
      }
    }
  }
</script>

<template>
  <v-form class="login" :class="{show: login}" v-model="form" @submit.prevent="cmslogin()">
    <v-card :loading="loading" class="elevation-2" :class="{failure: failure}">
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
        <v-alert v-show="failure" color="error" icon="mdi-alert-octagon" text="Login failed or user is not authorized!"></v-alert>
        <v-alert v-show="netfail" color="error" icon="mdi-alert-octagon" text="Server is not reachable or invalid response!"></v-alert>
      </v-card-text>

      <v-card-actions>
        <v-btn type="submit" variant="outlined" :disabled="form != true">Login</v-btn>
      </v-card-actions>
    </v-card>
  </v-form>
</template>

<style>
  body {
    background-image: url('src/assets/bg-full.png');
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

  .login .failure {
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
