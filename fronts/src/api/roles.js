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

export function editRole(data) {
  return http.post('/roles/edit', qs.stringify(data))
}

export function deleteRole(id) {
  return http.post('/roles/delete', qs.stringify({
    id: id
  }))
}

export function getPermissions(search) {
  return http.get('/roles/permissions')
}
