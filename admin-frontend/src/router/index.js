import { createRouter, createWebHistory } from 'vue-router'

const routes = [
  {
    path: '/login',
    name: 'login',
    component: () => import('../views/LoginView.vue'),
    meta: { guest: true },
  },
  {
    path: '/',
    component: () => import('../views/AdminLayout.vue'),
    meta: { requiresAuth: true },
    children: [
      {
        path: '',
        name: 'dashboard',
        component: () => import('../views/DashboardView.vue'),
      },
      {
        path: 'users',
        name: 'users',
        component: () => import('../views/UsersView.vue'),
      },
      {
        path: 'users/:id',
        name: 'user-detail',
        component: () => import('../views/UserDetailView.vue'),
      },
      {
        path: 'products',
        name: 'products',
        component: () => import('../views/ProductsView.vue'),
      },
      {
        path: 'products/:id',
        name: 'product-detail',
        component: () => import('../views/ProductDetailView.vue'),
      },
      {
        path: 'categories',
        name: 'categories',
        component: () => import('../views/CategoriesView.vue'),
      },
      {
        path: 'subcategories',
        name: 'subcategories',
        component: () => import('../views/SubcategoriesView.vue'),
      },
      {
        path: 'brands',
        name: 'brands',
        component: () => import('../views/BrandsView.vue'),
      },
      {
        path: 'models',
        name: 'models',
        component: () => import('../views/ModelsView.vue'),
      },
      {
        path: 'attributes',
        name: 'attributes',
        component: () => import('../views/AttributesView.vue'),
      },
    ],
  },
]

const router = createRouter({
  history: createWebHistory(),
  routes,
})

router.beforeEach((to) => {
  if (to.meta.guest) {
    if (localStorage.getItem('admin_token')) return { name: 'dashboard' }
    return true
  }

  if (to.meta.requiresAuth && !localStorage.getItem('admin_token')) {
    return { name: 'login' }
  }

  return true
})

export default router
