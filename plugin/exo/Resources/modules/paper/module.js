/**
 * Paper module
 */

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'at-table'

import './../common/module'

angular.module('Paper', [
    'ui.translation',
    'ui.bootstrap',
    'angular-table',
    'Common'
]);
