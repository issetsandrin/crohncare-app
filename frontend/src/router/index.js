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
    component: () => import('../pages/CadastroPage.vue'),
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
    path: '/configuracoes',
    name: 'configuracoes',
    component: () => import('../pages/ConfiguracoesPage.vue'),
    meta: { auth: true }
  },
  {
    path: '/avisos',
    name: 'avisos',
    component: () => import('../pages/AvisosPage.vue'),
    meta: { auth: true }
  },
  {
    path: '/onboarding',
    name: 'onboarding',
    component: () => import('../pages/OnboardingPage.vue'),
    meta: { auth: true }
  }
]

const router = createRouter({
  history: createWebHistory(),
  routes
})

router.beforeEach((to, from, next) => {
  const token = localStorage.getItem('token')
  const user = JSON.parse(localStorage.getItem('user') || 'null')

  if (to.meta.auth && !token) {
    next('/login')
  } else if (to.meta.guest && token) {
    next('/')
  } else if (token && user && !user.onboarding_completo && to.name !== 'onboarding') {
    next('/onboarding')
  } else {
    next()
  }
})

export default router
