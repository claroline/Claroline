'use strict';

var commentsApp = angular.module('commentsApp', ['ngResource', 'ngSanitize', 'ngAnimate', 'ui.tinymce', 'app.translation', 'app.filters']);

commentsApp.value('assetPath', window.assetPath);