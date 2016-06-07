/**
 * Question module
 */

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'ngBootbox'

import './../common/module'
import './../correction/module'
import './../image/module'

angular.module('Question', [
    'ui.translation',
    'ui.bootstrap',
    'ngBootbox',
    'Common',
    'Image',
    'Correction'
]);
