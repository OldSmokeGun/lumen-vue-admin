import http from '@/utils/http'
import qs from 'qs'

export function getRoles(search) {
  return http.get('/roles', {
    params: search
  })
}

export function createRole(data) {
  return http.post('/roles/create', qs.stringify(data))
}

export function updateRole(data) {
  return http.post('/roles/update', qs.stringify(data))
}

export function deleteRole(id) {
  return http.post('/roles/delete', qs.stringify({ id }))
}

export function getCreateRoleFormData() {
  return http.get('/roles/create')
}

export function getUpdateRoleFormData(id) {
  return http.get('/roles/update', {
    params: { id }
  })
}
