import { createRouter, createWebHistory } from 'vue-router'
import { useAppStore } from './stores'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
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
  if(to.matched.some(record => record.meta.auth)) {
    if (!useAppStore().me) {
      next({ name: 'login' })
    } else {
      next()
    }
  } else {
    next()
  }
})

export default router
