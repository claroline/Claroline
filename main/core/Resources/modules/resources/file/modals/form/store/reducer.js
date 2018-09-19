import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/core/resources/file/modals/form/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
