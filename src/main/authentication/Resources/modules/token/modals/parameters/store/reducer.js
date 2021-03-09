import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/authentication/token/modals/parameters/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
