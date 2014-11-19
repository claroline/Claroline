'use strict';

var commentsApp = angular.module('commentsApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce',
    'app.translation', 'app.filters', 'app.interpolator', 'app.directives']);

commentsApp.value('assetPath', window.assetPath);