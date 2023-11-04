const { defineConfig } = require('@vue/cli-service')
module.exports = defineConfig({
  transpileDependencies: true,
  publicPath: '/selfcare/selfcare-frontend/',
  productionSourceMap: false,
})
