import http from '@/utils/http'
import qs from 'qs'

export function login({ username, password }) {
  return http.post('/login', qs.stringify({
    username: username,
    password: password
  }))
}

export function logout(token) {
  return http.post('/logout', qs.stringify({
    token
  }))
}

export function getList(search) {
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

export function editAdmin(data) {
  return http.post('/admins/edit', qs.stringify(data))
}

export function deleteAdmin(id) {
  return http.post('/admins/delete', qs.stringify({
    id: id
  }))
}

export function getRoles() {
  return http.get('/admins/roles')
}
