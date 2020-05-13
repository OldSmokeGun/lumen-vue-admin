export default {
  'layout': () => import('@/layout/index'),
  'missing': () => import('@/components/error/missing'),
  'admins': () => import('@/views/admins/index'),
  'roles': () => import('@/views/roles/index'),
  'permissions': () => import('@/views/permissions/index'),
  'login': () => import('@/views/login/Login')
}
