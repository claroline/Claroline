import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/community/account/profile/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME)

export {
  reducer
}
