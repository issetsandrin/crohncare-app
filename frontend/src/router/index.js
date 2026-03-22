import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../pages/LoginPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/cadastro',
    name: 'cadastro',
    component: () => import('../pages/OnboardingPage.vue'),
    meta: { guest: true }
  },
  {
    path: '/',
    name: 'home',
    component: () => import('../pages/HomePage.vue'),
    meta: { auth: true }
  },
  {
    path: '/medicamentos',
    name: 'medicamentos',
    component: () => import('../pages/MedicamentosPage.vue'),
    meta: { auth: true }
  },
  {
    path: '/diario',
    name: 'diario',
    component: () => import('../pages/DiarioPage.vue'),
    meta: { auth: true }
  },
  {
    path: '/consultas',
    name: 'consultas',
    component: () => import('../pages/ConsultasPage.vue'),
    meta: { auth: true }
  },
  {
    path: '/perfil',
    name: 'perfil',
    component: () => import('../pages/PerfilPage.vue'),
    meta: { auth: true }
  },
  {
    path: '/avisos',
    name: 'avisos',
    component: () => import('../pages/AvisosPage.vue'),
    meta: { auth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')

  if (to.meta.auth && !token) {
    next('/login')
  } else if (to.meta.guest && token) {
    next('/')
  } else {
    next()
  }
})

export default router
