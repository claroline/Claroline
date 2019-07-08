import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/core/administration/parameters/technical/store/selectors'

const reducer = combineReducers({
  parameters: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'technical_settings')]: (state, action) => action.toolData.parameters
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'technical_settings')]: (state, action) => action.toolData.parameters
    })
  }),
  tools: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'technical_settings')]: (state, action) => action.toolData.tools
  })
})

export {
  reducer
}
