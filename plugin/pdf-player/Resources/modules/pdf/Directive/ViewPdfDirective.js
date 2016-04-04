import PdfController from '../Controller/PdfController'

export default class ViewPdfDirective {
    constructor() {
        this.restrict = 'E';
        this.template = require('../Partial/view_pdf.html');
        this.replace = true;
        this.controller = PdfController
        this.controllerAs = 'pdfc'
    }
}

PdfController.$inject = ['$http', '$scope']