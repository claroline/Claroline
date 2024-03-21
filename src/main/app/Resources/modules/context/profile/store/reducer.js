import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/app/context/profile/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
