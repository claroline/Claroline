/**
 * Paper module
 */

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'at-table/dist/angular-table'

import './../common/module'

angular.module('Paper', [
    'ui.translation',
    'ui.bootstrap',
    'angular-table',
    'Common'
]);
