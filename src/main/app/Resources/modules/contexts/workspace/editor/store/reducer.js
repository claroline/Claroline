import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {constants, makeFetchReducer} from '#/main/app/api/fetch'

import {selectors} from '#/main/app/contexts/workspace/editor/store/selectors'
import {makeInstanceAction} from '#/main/app/store/actions'
import {FORM_RESET} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  organizations: makeFetchReducer(selectors.STORE_NAME + '.organizations', {}, {
    status: makeReducer(constants.STATUS_IDLE, {
      [makeInstanceAction(FORM_RESET, selectors.STORE_NAME)]: () => constants.STATUS_IDLE
    })
  })
})

export {
  reducer
}
