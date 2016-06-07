/**
 * Step module
 */

import angular from "angular/index"
import registerDragula from "angular-dragula/dist/angular-dragula"

import 'angular-bootstrap'
import 'angular-ui-translation/angular-translation'
import 'angular-ui-tinymce'
import 'ngBootbox'

import './../common/module'
import './../question/module'

registerDragula(angular)

angular.module('Step', [
    'ui.translation',
    'ui.bootstrap',
    'ui.tinymce',
    'ngBootbox',
    'dragula',
    'Common',
    'Question'
]);
