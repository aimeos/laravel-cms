import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from './stores'

const router = createRouter({
  history: createWebHistory(document.querySelector('#app')?.dataset?.urlbase || ''),
  routes: [
    {
      path: '/',
      name: 'login',
      component: () => import('./views/LoginView.vue')
    },
    {
      path: '/pages',
      name: 'pages',
      component: () => import('./views/PagesView.vue'),
      meta: {
        auth: true
      }
    },
    {
      path: '/files',
      name: 'files',
      component: () => import('./views/FilesView.vue'),
      meta: {
        auth: true
      }
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const store = useAuthStore()
  const authenticated = await store.isAuthenticated()

  if(to.matched.some(record => record.meta.auth) && !authenticated) {
    store.intended(to.fullPath)
    next({name: 'login'})
  } else {
    next()
  }
})

export default router
