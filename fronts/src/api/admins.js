import http from '@/utils/http'
import qs from 'qs'

export function login({ username, password }) {
  return http.post('/login', qs.stringify({
    username,
    password
  }))
}

export function logout(token) {
  return http.post('/logout', qs.stringify({
    token
  }))
}

export function updatePassword(data) {
  return http.post('/admins/update-password', qs.stringify(data))
}

export function getAdmins(search) {
  return http.get('/admins', {
    params: search
  })
}

export function getAdminInfo(token) {
  return http.get('/admins/info', {
    params: {
      token
    }
  })
}

export function createAdmin(data) {
  return http.post('/admins/create', qs.stringify(data))
}

export function updateAdmin(data) {
  return http.post('/admins/update', qs.stringify(data))
}

export function deleteAdmin(id) {
  return http.post('/admins/delete', qs.stringify({ id }))
}

export function resetPassword(id) {
  return http.post('/admins/reset-password', qs.stringify({ id }))
}

export function getCreateAdminFormData() {
  return http.get('/admins/create')
}

export function getUpdateAdminFormData(id) {
  return http.get('/admins/update', {
    params: { id }
  })
}
