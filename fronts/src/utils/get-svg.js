const files = require.context('../icons/svg', false, /\.svg$/)
const icons = []

files.keys().forEach(key => {
  icons.push(key.replace(/(\.\/|\.svg)/g, ''))
})

export default icons
