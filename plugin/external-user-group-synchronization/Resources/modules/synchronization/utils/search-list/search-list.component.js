/**
 * Created by panos on 6/1/17.
 */
import searchListTemplate from './search-list.partial.html'
import { SearchListController } from './search-list.controller'

export const SearchListComponent = {
  template: searchListTemplate,
  controller: SearchListController,
  bindings: {
    items: '<',
    totalItems: '<',
    fieldNames: '<',
    actions: '<?',
    orderBy: '@',
    onChange: '&',
    onInit: '&'
  }
}