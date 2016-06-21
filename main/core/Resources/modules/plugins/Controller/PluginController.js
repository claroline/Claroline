export default class PluginController {
  constructor ($http, $uibModal) {
    this.$http = $http
    this.$uibModal = $uibModal

    this.plugins = []
    $http.get(Routing.generate('api_get_plugins')).then(d => this.plugins = d.data)
  }

  onCheckboxChange (plugin) {
    plugin.is_loaded ? this.enable(plugin) : this.disable(plugin)
  }

  enable (plugin) {
    this.$http.patch(Routing.generate('api_enable_plugin', {plugin: plugin.id})).then(d => this.plugins = d.data)
  }

  disable (plugin) {
    this.$http.patch(Routing.generate('api_disable_plugin', {plugin: plugin.id})).then(d => this.plugins = d.data)
  }

  openPluginConfiguration (plugin) {
    const route = Routing.generate('claro_admin_plugin_parameters', {pluginShortName: plugin.bundle})
    // no angular support yet so we do a simple redirect.
    window.location = route
  }

  onCacheWarningClick () {
    const modal = this.$uibModal.open({
      template: require('../Partial/cache.html')
    })
  }

  onWarningClick (plugin) {
    const modal = this.$uibModal.open({
      template: require('../Partial/warning.html'),
      controller: 'WarningController',
      controllerAs: 'cwpc',
      resolve: {
        plugin: () => {
          return plugin}
      }
    })
  }
}
