import externalSourceGroupConfigTemplate from './groupconfig.partial.html'
import externalSourceGroupConfigSimpleTemplate from './groupconfig-simple.partial.html'
import externalSourceGroupConfigController from './groupconfig.controller'

export default class ExternalSourceGroupConfigDirective {
  constructor(tableNames) {
    this._tableNames = tableNames

    this.restrict = 'E'
    this.scope = {}
    this.template = this._tableNames.length > 0 ? externalSourceGroupConfigTemplate : externalSourceGroupConfigSimpleTemplate
    this.controller  = externalSourceGroupConfigController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}

ExternalSourceGroupConfigDirective.$inject = ['tableNames']