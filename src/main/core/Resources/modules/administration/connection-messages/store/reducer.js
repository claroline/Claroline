import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from "#/main/app/store/actions";
import {TOOL_LOAD}                    from "#/main/core/tool/store";

const reducer = combineReducers({
  lockedParameters: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.lockedParameters
  }),
  parameters: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.parameters || {}
  }),
  availableLocales: makeReducer([], {
    [makeInstanceAction(TOOL_LOAD, 'main_settings')]: (state, action) => action.toolData.availableLocales
  })
})

export { reducer }
