import componentsMap from '@/router/map'
import { baseRoutes, resetRouter } from '@/router/routes'

const defaultState = () => {
  return {
    routes: [],
    addRoutes: [],
    maps: []
  }
}

export default {
  namespaced: true,
  state: defaultState(),
  actions: {
    setRoutes({ commit }, permissions) {
      return new Promise(resolve => {
        const generateRoutes = (routes) => {
          const addRoutes = []
          for (const item of routes) {
            const route = {}
            route.path = item.identification
            if (!Number(item.parent_id)) {
              route.component = componentsMap['layout']
            } else {
              route.component = (item.identification in componentsMap) ? componentsMap[item.identification] : componentsMap['missing']
            }
            route.meta = { title: item.title, icon: item.icon }
            route.redirect = item.redirect
            route.hidden = !item.display
            if (item.children.length) {
              route.children = generateRoutes(item.children)
            }
            addRoutes.push(route)
          }
          return addRoutes
        }

        const addRoutes = generateRoutes(permissions.routes)

        addRoutes.push({
          path: '*',
          name: '404',
          component: () => import('@/components/error/404'),
          hidden: true
        })

        commit('SET_ROUTES', { addRoutes, maps: permissions.maps })
        resolve(addRoutes)
      })
    },
    resetRoutes({ commit }) {
      return new Promise(resolve => {
        commit('RESET_ROUTES')
        resolve()
      })
    }
  },
  mutations: {
    SET_ROUTES(state, routes) {
      state.routes = [...baseRoutes, ...routes.addRoutes]
      state.addRoutes = routes.addRoutes
      state.maps = routes.maps
    },
    RESET_ROUTES(state) {
      Object.assign(state, defaultState())
      resetRouter()
    }
  }
}
