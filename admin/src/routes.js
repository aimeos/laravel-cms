import { createRouter, createWebHistory } from 'vue-router'
import { useAppStore } from './stores'

const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: window.location.pathname + '/',
      name: 'login',
      component: () => import('./views/LoginView.vue')
    },
    {
      path: window.location.pathname + '/pages',
      name: 'pages',
      component: () => import('./views/PagesView.vue'),
      meta: {
        auth: true
      }
    },
    {
      path: window.location.pathname + '/files',
      name: 'files',
      component: () => import('./views/FilesView.vue'),
      meta: {
        auth: true
      }
    }
  ]
})

router.beforeEach((to, from, next) => {
  if(to.matched.some(record => record.meta.auth) && !useAppStore().me) {
      next({ name: 'login' })
  } else {
    next()
  }
})

export default router
