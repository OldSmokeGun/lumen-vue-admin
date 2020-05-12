import store from '@/store'

export default {
  install: function(Vue, options) {
    Vue.directive('permission', {
      inserted(el, binding) {
        if (binding.value) {
          const permissionMaps = store.getters.permissionMaps
          let hasPermission = false
          if (typeof binding.value === 'string') {
            hasPermission = permissionMaps.some((permission) => {
              return String(permission.identification) === String(binding.value)
            })
          } else if (Array.isArray(binding.value)) {
            let matchCount = 0
            permissionMaps.some((permission) => {
              binding.value.some((val) => {
                if (String(permission.identification) === String(val)) {
                  matchCount += 1
                }
              })
            })

            if (binding.modifiers.checkAll) {
              if (Number(matchCount) === binding.value.length) {
                hasPermission = true
              }
            } else {
              if (Number(matchCount) > 0) {
                hasPermission = true
              }
            }
          } else {
            throw new Error(`权限标识类型为 String 或 Array`)
          }

          if (!hasPermission) {
            el.parentNode && el.parentNode.removeChild(el)
          }
        } else {
          throw new Error(`缺少权限标识`)
        }
      }
    })

    Vue.prototype.$permission = function(identification, checkAll = false) {
      if (identification) {
        const permissionMaps = store.getters.permissionMaps
        let hasPermission = false

        if (typeof identification === 'string') {
          hasPermission = permissionMaps.some((permission) => {
            return String(permission.identification) === String(identification)
          })
        } else if (Array.isArray(identification)) {
          let matchCount = 0
          permissionMaps.some((permission) => {
            identification.some((val) => {
              if (String(permission.identification) === String(val)) {
                matchCount += 1
              }
            })
          })

          if (checkAll) {
            if (Number(matchCount) === identification.length) {
              hasPermission = true
            }
          } else {
            if (Number(matchCount) > 0) {
              hasPermission = true
            }
          }
        } else {
          console.error(`权限标识类型为 String 或 Array`)
          return false
        }

        if (!hasPermission) {
          return false
        }
        return true
      } else {
        console.error(`缺少权限标识`)
        return false
      }
    }
  }
}
