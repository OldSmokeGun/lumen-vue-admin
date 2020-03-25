import router from '@/router'
import store from '@/store'
import { MessageBox } from 'element-ui'
import NProgress from 'nprogress'
import 'nprogress/nprogress.css'
import { getToken } from '@/utils/cookie'

NProgress.configure({ showSpinner: false })

const whiteList = ['/login']

const errorAlert = (to, next) => {
  MessageBox.alert('身份信息异常，请重新登陆', {
    showClose: false,
    type: 'warning'
  }).then(async() => {
    await store.dispatch('admin/resetInfo')
    await store.dispatch('permissions/resetRoutes')
    next(`/login?redirect=${to.path}`)
  })
}

router.beforeEach(async(to, from, next) => {
  NProgress.start()
  document.title = to.meta.title ? `${to.meta.title} - ${store.getters.setting.title}` : store.getters.setting.title

  if (getToken()) {
    if (to.path === '/login') {
      next({ path: '/' })
    } else {
      if (store.getters.adminInfo.id) {
        next()
      } else {
        try {
          const adminInfo = await store.dispatch('admin/getInfo')
          const addRoutes = await store.dispatch('permissions/setRoutes', adminInfo.permission_maps)
          router.addRoutes(addRoutes)
          next({ ...to, replace: true })
        } catch (error) {
          if (Object.keys(error).length === 0) {
            errorAlert(to, next)
          }
          NProgress.done()
        }
      }
    }
  } else {
    if (whiteList.indexOf(to.path) !== -1) {
      next()
    } else {
      errorAlert(to, next)
      NProgress.done()
    }
  }
})

router.afterEach(() => {
  NProgress.done()
})
