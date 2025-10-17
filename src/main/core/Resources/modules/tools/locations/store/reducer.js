import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {CONTEXT_OPEN} from '#/main/app/context/store/actions'
import {TOOL_OPEN} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/core/tools/locations/store/selectors'
import {LOCATION_LOAD} from '#/main/core/tools/locations/store/actions'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME+'.list', {}, {
    loaded: makeReducer(false, {
      [CONTEXT_OPEN]: () => false
    }),
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true
    })
  }),
  current: makeReducer(null, {
    [LOCATION_LOAD]: (state, action) => action.location
  })
})

export {
  reducer
}
