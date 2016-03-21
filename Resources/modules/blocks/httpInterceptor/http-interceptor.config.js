(function () {
  'use strict';

  angular
    .module("blocks.httpInterceptor")
    .config(config);

  config.$inject = [ '$httpProvider' ];
  function config($httpProvider) {
    $httpProvider.interceptors.push('httpInterceptor');
  }
})();
