import PdfController from '../Controller/PdfController'

import template from './../Partial/view_pdf.html'

export default class ViewPdfDirective {
  constructor() {
    this.restrict = 'E'
    this.template = template
    this.replace = true
    this.controller = PdfController
    this.controllerAs = 'pdfc'
    this.bindToController = true
    this.scope = {
      id: '=',
      name: '=',
      url: '='
    }
  }
}

PdfController.$inject = ['$http', '$scope', 'url']