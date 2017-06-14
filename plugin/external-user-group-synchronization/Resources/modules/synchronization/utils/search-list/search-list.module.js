import angular from 'angular/index'
import {} from 'angular-bootstrap'
import {} from 'angular-ui-translation/angular-translation'
import { camelToSnake } from './camel-to-underscore.filter'
import { SearchListComponent } from './search-list.component'

export const SearchListModule = angular
  .module('search.list.module', [
    'ui.translation',
    'ui.bootstrap'
  ])
  .component('searchList', SearchListComponent)
  .filter('camel2snake', () => camelToSnake)
  .name

