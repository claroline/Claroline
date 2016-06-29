import indexTemplate from './index.partial.html'
import searchTemplate from './search.partial.html'
import indexController from './index.controller'
import searchController from './search.controller'

export default function Router($routeProvider, $locationProvider) {
  $routeProvider
    .when('/', {
      template: indexTemplate,
      controllerAs: 'vm',
      controller: indexController
    })
    .when('/search/:resourceType', {
      template: searchTemplate,
      controllerAs: 'vm',
      controller: searchController
    })
    .otherwise('/')
  $locationProvider.html5Mode(true)
}
Router.$inject = ['$routeProvider', '$locationProvider']