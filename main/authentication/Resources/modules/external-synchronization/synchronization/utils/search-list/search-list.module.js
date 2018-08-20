import angular from 'angular/index'
import {} from 'angular-ui-bootstrap'
import {} from '#/main/core/innova/angular-translation'
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

