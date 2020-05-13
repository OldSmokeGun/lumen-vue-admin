const getters = {
  sidebar: state => state.app.sidebar,
  device: state => state.app.device,
  setting: state => state.app.setting,
  adminInfo: state => state.admins,
  permissionRoutes: state => state.permissions.routes,
  permissionMaps: state => state.permissions.maps
}
export default getters
