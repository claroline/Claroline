import {BookReferenceConfiguration} from '#/plugin/bibliography/configuration/components/configuration'
import {reducer} from '#/plugin/bibliography/configuration/store'

/**
 * Book reference configuration application.
 *
 * @constructor
 */
export const App = () => ({
  component: BookReferenceConfiguration,
  store: reducer,
  styles: 'claroline-distribution-plugin-bibliography-book-reference-configuration',
  initialData: initialData => Object.assign({}, initialData, {
    bookReferenceConfiguration: {
      data: initialData.bookReferenceConfiguration,
      originalData: initialData.bookReferenceConfiguration
    }
  })
})