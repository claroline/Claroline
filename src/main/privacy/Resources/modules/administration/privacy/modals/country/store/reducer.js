import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from '#/main/privacy/administration/privacy/modals/country/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
