import { createRouter, createWebHistory } from 'vue-router'
import { useAppStore } from './stores'

const router = createRouter({
  history: createWebHistory(window.location.pathname),
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

router.beforeEach((to, from, next) => {
  if(to.matched.some(record => record.meta.auth) && !useAppStore().me) {
      next({ name: 'login' })
  } else {
    next()
  }
})

export default router
