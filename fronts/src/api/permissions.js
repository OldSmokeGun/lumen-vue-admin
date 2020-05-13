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

export function editPermission(data) {
  return http.post('/permissions/edit', qs.stringify(data))
}

export function deletePermission(id) {
  return http.post('/permissions/delete', qs.stringify({
    id: id
  }))
}

export function getPermissionTrees(type) {
  return http.get('/permissions/trees', {
    params: {
      type: type
    }
  })
}
