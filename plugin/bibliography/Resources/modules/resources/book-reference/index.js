import {reducer} from '#/plugin/bibliography/resources/book-reference/store'
import {BookReferenceResource} from '#/plugin/bibliography/resources/book-reference/containers/resource'

/**
 * Book reference resource application.
 *
 * @constructor
 */
export default {
  component: BookReferenceResource,
  store: reducer
}