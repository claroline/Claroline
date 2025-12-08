import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {constants, makeFetchReducer} from '#/main/app/api/fetch'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store'
import {FORM_RESET} from '#/main/app/content/form/store/actions'

import {selectors} from '#/plugin/open-badge/badge/editor/store/selectors'

const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME),
  organizations: makeFetchReducer(selectors.STORE_NAME + '.organizations', {}, {
    status: makeReducer(constants.STATUS_IDLE, {
      [makeInstanceAction(FORM_RESET, selectors.STORE_NAME)]: () => constants.STATUS_IDLE
    })
  })
})

export {
  reducer
}
