import store from '@/store'

export default function permission() {

  if (binding.value && typeof binding.value === 'string') {

    const permissionMaps = store.getters.permissionMaps

    const hasPermission = permissionMaps.some((permission) => {
      return String(permission.identification) === String(binding.value)
    })

    if (!hasPermission) {
      return false
    }

    return true

  } else {
    console.error(`缺少权限标识`)
    return false
  }

}
