import axios from 'axios'
import { Message, MessageBox } from 'element-ui'
import router from '@/router'
import store from '@/store'
import { getToken } from '@/utils/cookie'

const instance = axios.create({
  baseURL: '/api',
  timeout: 3000,
  headers: {
    'X-Requested-With': 'XMLHttpRequest'
  }
})

instance.interceptors.request.use(config => {
  config.headers['Authorization'] = getToken() ? getToken() : ''

  return config
}, error => {
  return Promise.reject(error)
})

instance.interceptors.response.use(response => {
  const res = response.data

  if (String(res.code) === 'ERROR') {
    Message.warning(res.message)
  } else if (String(res.code) === 'LOGIN_VALID') {
    MessageBox.alert('登录已失效，请重新登陆', {
      showClose: false,
      type: 'warning'
    }).then(async() => {
      await store.dispatch('admin/resetInfo')
      await store.dispatch('permissions/resetRoutes')
      router.push({ path: '/login' })
    })
    return Promise.reject(new Error(res.message))
  } else if (String(res.code) === 'PERMISSION_DENY') {
    Message.warning(res.message)
    return Promise.reject(new Error(res.message))
  } else if (String(res.code) === 'ILLEGAL_REQUEST') {
    Message.warning(res.message)
    return Promise.reject(new Error(res.message))
  }

  return response
}, error => {
  Message.error(`${error.response.status}: ${error.response.statusText}`)
  return Promise.reject(error)
})

export default instance
