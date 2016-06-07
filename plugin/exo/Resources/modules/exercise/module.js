/**
 * Exercise Module
 */

import 'angular-bootstrap'
import 'angular-strap'
import 'angular-ui-tinymce'
import 'angular-ui-translation/angular-translation'
import 'ngBootbox'

import './../common/module'
import './../step/module'
import './../paper/module'
import './../timer/module'

angular.module('Exercise', [
    'ui.translation',
    'ui.bootstrap',
    'ui.tinymce',
    'ngBootbox',
    'mgcrea.ngStrap.datepicker',
    'Common',
    'Step',
    'Paper',
    'Timer'
]);
