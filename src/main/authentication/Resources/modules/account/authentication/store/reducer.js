import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/authentication/account/authentication/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
