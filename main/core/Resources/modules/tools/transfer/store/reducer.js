import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/tools/transfer/store/selectors'
import {reducer as logReducer} from '#/main/core/tools/transfer/log/store/reducer'

const reducer = combineReducers({
  explanation: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.explanation
  }),
  samples: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.samples
  }),
  import: makeFormReducer(selectors.STORE_NAME + '.import'),
  export: makeFormReducer(selectors.STORE_NAME + '.export'),
  history: makeListReducer(selectors.STORE_NAME + '.history', {
    sortBy: {property: 'uploadDate', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  log: logReducer
})

export {
  reducer
}
