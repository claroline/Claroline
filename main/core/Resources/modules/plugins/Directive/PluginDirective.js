import ClarolinePluginController from '../Controller/PluginController'

export default class ClarolineSearchDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('../Partial/plugins.html')
    this.replace = false
    this.controller = ClarolinePluginController
    this.controllerAs = 'cpc'
  }
}
