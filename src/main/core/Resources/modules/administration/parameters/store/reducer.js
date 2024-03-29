import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/administration/parameters/store/selectors'

import {reducer as themeReducer, selectors as themeSelectors} from '#/main/theme/administration/appearance/store'

const reducer = combineReducers({
  lockedParameters: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'parameters')]: (state, action) => action.toolData.lockedParameters
  }),
  parameters: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'parameters')]: (state, action) => action.toolData.parameters
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'parameters')]: (state, action) => action.toolData.parameters
    })
  }),
  availableLocales: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'parameters')]: (state, action) => action.toolData.availableLocales
  }),
  // for appearance. Should be injected by ThemeBundle later
  [themeSelectors.STORE_NAME]: themeReducer
})

export {
  reducer
}
