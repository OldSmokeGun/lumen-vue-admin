import { getToken, setToken, removeToken } from '@/utils/cookie'
import { getAdminInfo } from '@/api/admins'

const defaultState = () => {
  return {
    id: '',
    username: '',
    nickname: '',
    avatar: '',
    email: '',
    token: '',
    last_login_date: '',
    last_login_ip: '',
    permissions_map: {}
  }
}

export default {
  namespaced: true,
  state: defaultState(),
  actions: {
    setToken({ commit }, token) {
      return new Promise(resolve => {
        commit('SET_TOKEN', token)
        resolve()
      })
    },
    getInfo({ commit }) {
      return new Promise((resolve, reject) => {
        getAdminInfo(getToken())
          .then(response => {
            if (response.data.code === 'OK') {
              const data = response.data.data
              if (data.length === 0) {
                reject({})
              } else {
                commit('SET_INFO', data)
                resolve(data)
              }
            }
          })
          .catch(e => { reject(e) })
      })
    },
    resetInfo({ commit }) {
      return new Promise(resolve => {
        commit('RESET_INFO')
        resolve()
      })
    }
  },
  mutations: {
    SET_TOKEN(state, token) {
      state.token = token
      setToken(token)
    },
    SET_INFO(state, adminInfo) {
      Object.assign(state, adminInfo)
    },
    RESET_INFO(state) {
      Object.assign(state, defaultState())
      removeToken()
    }
  }
}
