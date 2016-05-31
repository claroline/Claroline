import ProfileController from '../Controller/ProfileController'

export default class ProfileDirective {
  constructor () {
    this.scope = {}
    this.restrict = 'E'
    this.template = require('../Partial/profile.html')
    this.replace = false
    this.controller = ProfileController
    this.controllerAs = 'pc'
  }
}
