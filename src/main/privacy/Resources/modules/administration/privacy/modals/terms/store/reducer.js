import {selectors} from '#/main/privacy/administration/privacy/modals/terms/store/selectors'
import {makeFormReducer} from '#/main/app/content/form/store'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
