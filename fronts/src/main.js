import Vue from 'vue'
import router from './router'
import store from './store'
import App from './App'

import ElementUI from 'element-ui'
import 'element-ui/lib/theme-chalk/index.css'

import 'normalize.css/normalize.css'
import './styles/index.scss'
import './icons'
import './router/guard'

import Permission from '@/plugins/permission'

Vue.use(ElementUI)
Vue.use(Permission)

Vue.config.productionTip = false

new Vue({
  router,
  store,
  render: h => h(App)
}).$mount('#app')
