import ColorpickerController from './bs-colorpicker.controller'
import colorpickerTemplate from './bs-colorpicker.partial.html'

export default class bsColorpicker {
  constructor () {
    this.restrict = 'EA'
    this.scope = {'color': "=color"}
    this.template = colorpickerTemplate
    this.controller = ColorpickerController
    this.controllerAs = 'vm'
    this.bindToController = true
  }
}

