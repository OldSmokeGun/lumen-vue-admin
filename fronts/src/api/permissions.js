import http from '@/utils/http'
import qs from 'qs'

export function getPermissions(search) {
  return http.get('/permissions', {
    params: search
  })
}

export function createPermission(data) {
  return http.post('/permissions/create', qs.stringify(data))
}

export function updatePermission(data) {
  return http.post('/permissions/update', qs.stringify(data))
}

export function deletePermission(id) {
  return http.post('/permissions/delete', qs.stringify({ id }))
}

export function getCreatePermissionFormData() {
  return http.get('/permissions/create')
}

export function getUpdatePermissionFormData(id) {
  return http.get('/permissions/update', {
    params: { id }
  })
}
