export default {
  'layout': () => import('@/layout/index'),
  'missing': () => import('@/components/error/missing'),
  '/system/admins': () => import('@/views/admins/index'),
  '/system/roles': () => import('@/views/roles/index'),
  '/system/permissions': () => import('@/views/permissions/index'),
  'login': () => import('@/views/login/Login')
}
