import {BookReferenceResource} from '#/plugin/bibliography/resources/book-reference/components/resource'
import {reducer} from '#/plugin/bibliography/resources/book-reference/store'

/**
 * Wiki resource application.
 *
 * @constructor
 */
export const App = () => ({
  component: BookReferenceResource,
  store: reducer,
  styles: 'claroline-distribution-plugin-bibliography-book-reference-resource',
  initialData: initialData => Object.assign({}, initialData, {
    bookReference: {
      data: initialData.bookReference,
      originalData: initialData.bookReference
    },
    resource: {
      node: initialData.resourceNode,
      evaluation: initialData.evaluation
    }
  })
})