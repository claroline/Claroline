import {combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {selectors} from '#/main/authentication/account/tokens/store/selectors'

const reducer = makeListReducer(selectors.STORE_NAME)

export {
  reducer
}
