import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/tools/parameters/store/selectors'

const reducer = combineReducers({
  list: makeListReducer('tokens.list', {}, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  current: makeFormReducer('tokens.current', {}, {
  })
})

export {
  reducer
}
