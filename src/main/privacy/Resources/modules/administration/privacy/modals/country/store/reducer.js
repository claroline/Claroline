import {selectors} from '#/main/privacy/administration/privacy/modals/country/store/selectors'
import {makeFormReducer} from '#/main/app/content/form/store'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {TOOL_LOAD} from '#/main/core/tool/store'

const reducer = combineReducers({
  parameters: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.FORM_NAME)]: (state, action) => action.toolData.parameters
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.FORM_NAME)]: (state, action) => action.toolData.parameters
    })
  })
})

export {
  reducer
}
