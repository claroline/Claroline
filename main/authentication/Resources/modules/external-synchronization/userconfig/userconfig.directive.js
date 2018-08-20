import externalSourceUserConfigTemplate from './userconfig.partial.html'
import externalSourceUserConfigSimpleTemplate from './userconfig-simple.partial.html'
import externalSourceUserConfigController from './userconfig.controller'

export default class ExternalSourceUserConfigDirective {
  constructor(tableNames) {
    this._tableNames = tableNames

    this.restrict = 'E'
    this.scope = {}
    this.template = this._tableNames.length > 0 ? externalSourceUserConfigTemplate : externalSourceUserConfigSimpleTemplate
    this.controller  = externalSourceUserConfigController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}

ExternalSourceUserConfigDirective.$inject = ['tableNames']