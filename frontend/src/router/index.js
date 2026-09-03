import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/',
    name: 'home',
    component: () => import('../views/HomeView.vue'),
  },
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/auth/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/register',
    name: 'register',
    component: () => import('../views/auth/RegisterView.vue'),
    meta: { guest: true },
  },
  {
    path: '/verify-email',
    name: 'verify-email',
    component: () => import('../views/auth/VerifyEmailView.vue'),
  },
  {
    path: '/forgot-password',
    name: 'forgot-password',
    component: () => import('../views/auth/ForgotPasswordView.vue'),
    meta: { guest: true },
  },
  {
    path: '/reset-password',
    name: 'reset-password',
    component: () => import('../views/auth/ResetPasswordView.vue'),
  },
  {
    path: '/set-password',
    name: 'set-password',
    component: () => import('../views/auth/SetPasswordView.vue'),
  },
  {
    path: '/profile',
    name: 'profile',
    component: () => import('../views/ProfileView.vue'),
    meta: { requiresAuth: true },
  },
  {
    path: '/profile-setup',
    name: 'profile-setup',
    component: () => import('../views/auth/ProfileSetupView.vue'),
    meta: { requiresAuth: true },
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

const PUBLIC_ROUTES = ['/login', '/register', '/forgot-password', '/set-password']

router.beforeEach((to) => {
  if (to.meta.guest) {
    if (localStorage.getItem('token')) return { name: 'home' }
    return true
  }

  if (to.meta.requiresAuth && !localStorage.getItem('token')) {
    return { name: 'login' }
  }

  return true
})

export default router
