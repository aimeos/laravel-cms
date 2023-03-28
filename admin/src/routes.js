import { createRouter, createWebHistory } from 'vue-router'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: () => import('./views/HomeView.vue')
    },
    {
      path: '/pages',
      name: 'pages',
      component: () => import('./views/PagesView.vue')
    },
    {
      path: '/files',
      name: 'files',
      component: () => import('./views/FilesView.vue')
    }
  ]
})

export default router
