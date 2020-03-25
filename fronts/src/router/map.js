export default {
  'layout': () => import('@/layout/index'),
  'missing': () => import('@/components/error/missing'),
  'admin': () => import('@/views/admin/index'),
  'role': () => import('@/views/role/index'),
  'permission': () => import('@/views/permission/index'),
  'login': () => import('@/views/login/Login')
}
