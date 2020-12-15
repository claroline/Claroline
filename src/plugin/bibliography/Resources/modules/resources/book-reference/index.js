import {reducer} from '#/plugin/bibliography/resources/book-reference/store'
import {BookReferenceResource} from '#/plugin/bibliography/resources/book-reference/containers/resource'
import {BookReferenceMenu} from '#/plugin/bibliography/resources/book-reference/components/menu'

/**
 * Book reference resource application.
 *
 * @constructor
 */
export default {
  component: BookReferenceResource,
  menu: BookReferenceMenu,
  store: reducer
}