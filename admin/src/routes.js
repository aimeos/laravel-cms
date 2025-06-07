import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore, useMessageStore } from './stores'

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
      name: 'page:view',
      component: () => import('./components/PageList.vue'),
      meta: {
        auth: true
      }
    },
    {
      path: '/elements',
      name: 'element:view',
      component: () => import('./components/ElementList.vue'),
      meta: {
        auth: true
      }
    },
    {
      path: '/files',
      name: 'file:view',
      component: () => import('./components/FileList.vue'),
      meta: {
        auth: true
      }
    }
  ]
})

router.beforeEach(async (to, from, next) => {
  const auth = useAuthStore()
  const message = useMessageStore()
  const authenticated = await auth.isAuthenticated()

  if(to.matched.some(record => record.meta.auth) && !authenticated) {
    auth.intended(to.fullPath)
    next({name: 'login'})
  } else if(to.name !== 'login' && !auth.can(to.name)) {
    message.add('You do not have permission to access ' + to.fullPath, 'error')
    return next(false)
  } else {
    next()
  }
})

export default router
