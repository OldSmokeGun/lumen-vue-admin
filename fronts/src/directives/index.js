import Vue from 'vue'
import store from '@/store'

Vue.directive('permission', {
  inserted(el, binding) {
    if (binding.value && typeof binding.value === 'string') {
      const permissionMaps = store.getters.permissionMaps

      const hasPermission = permissionMaps.some((permission) => {
        return String(permission.identification) === String(binding.value)
      })

      if (!hasPermission) {
        el.parentNode && el.parentNode.removeChild(el)
      }
    } else {
      throw new Error(`缺少权限标识`)
    }
  }
})
