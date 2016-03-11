import 'angular/angular.min'

import translation from 'angular-ui-translation/angular-translation'

import ViewPdfDirective from './Directive/ViewPdfDirective'

angular.module('PdfViewer', ['ui.translation'])
    .directive('pdfviewer', () => new ViewPdfDirective)
