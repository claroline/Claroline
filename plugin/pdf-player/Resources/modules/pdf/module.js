import angular from 'angular/index'
import '#/main/core/innova/angular-translation'

import '#/main/core/api/router/module'
import ViewPdfDirective from './Directive/ViewPdfDirective'

angular
  .module('PdfViewer', [
    'ui.translation',
    'ui.fos-js-router'
  ])
  .directive('pdfViewer', () => new ViewPdfDirective)
