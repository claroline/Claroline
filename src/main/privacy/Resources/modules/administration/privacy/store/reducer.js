
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from './selectors'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  new: true
})

export {
  reducer
}

/*
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from './selectors'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'

const reducer = combineReducers({
  lockedParameters: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.lockedParameters
  }),
  parameters: makeFormReducer(selectors.STORE_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.parameters
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.parameters
    })
  }),
  availableLocales: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.availableLocales
  })
})

export {
  reducer
}
*/
