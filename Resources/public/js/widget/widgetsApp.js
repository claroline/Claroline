'use strict';

var widgetsApp = angular.module('widgetsApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce',
    'ui.resourcePicker', 'ui.badgePicker', 'ui.datepicker', 'ui.dateTimeInput', 'mgcrea.ngStrap.popover',
    'ui.bootstrap', 'app.translation', 'app.interpolator', 'app.directives']);

widgetsApp.value('assetPath', window.assetPath);