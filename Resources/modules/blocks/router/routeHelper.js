(function () {
  'use strict';

  angular
    .module('blocks.router')
    .provider('routeHelperConfig', routeHelperConfig)
    .factory('routeHelper', routeHelper);

  routeHelper.$inject = [ '$location', '$rootScope', '$route', 'routeHelperConfig' ];

  function routeHelperConfig() {
    this.config = {};

    this.$get = function () {
      return {
        config: this.config
      }
    };
  }

  function routeHelper($location, $rootScope, $route, routeHelperConfig) {
    var routes = [];
    var $routeProvider = routeHelperConfig.config.$routeProvider;

    var service = {
      configureRoutes: configureRoutes,
      getRoutes: getRoutes
    }

    init();

    return service;
    ////////////////

    function configureRoutes(routes) {
      routes.forEach(
        function (route) {
          route.config.resolve =
            angular.extend(route.config.resolve || {}, routeHelperConfig.config.resolveAlways);
          $routeProvider.when(route.url, route.config);
        }
      );
      $routeProvider.otherwise({redirectTo: '/structure'});
    }

    function handleRoutingErrors() {
      $rootScope.$on('$routeChangeError',
        function (event, current, previous, rejection) {
          $location.parth('/structure');
        }
      );
    }

    function init() {
      handleRoutingErrors();
      updateOptionWindow();
    }

    function getRoutes() {
      for (var prop in $route.routes) {
        if ($route.routes.hasOwnProperty(prop)) {
          var route = $route.routes[ prop ];
          var isRoute = !!route.option;
          if (isRoute) {
            routes.push(route);
          }
        }
      }
      return routes;
    }

    function updateOptionWindow() {
      $rootScope.$on('$routeChangeSuccess',
        function (event, current) {
          $rootScope.optionWindow = current.option;
        }
      );
    }
  }
})();