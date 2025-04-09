import { createRouter, createWebHistory } from 'vue-router'
import { useAppStore } from './stores'

const url = document.querySelector('#app')?.dataset?.urlbase || ''
const router = createRouter({
  history: createWebHistory(),
  routes: [
    {
      path: url + '/',
      name: 'login',
      component: () => import('./views/LoginView.vue')
    },
    {
      path: url + '/pages',
      name: 'pages',
      component: () => import('./views/PagesView.vue'),
      meta: {
        auth: true
      }
    },
    {
      path: url + '/files',
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
