import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {selectors} from '#/main/privacy/administration/privacy/store/selectors'
import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

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
  })
})

export {
  reducer
}

