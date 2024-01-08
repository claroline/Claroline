import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'

import {selectors} from '#/main/privacy/administration/privacy/store/selectors'

const reducer = combineReducers({
  parameters: makeFormReducer(selectors.FORM_NAME, {}, {
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.privacy
    }),
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, 'privacy')]: (state, action) => action.toolData.privacy
    })
  })
})

export {
  reducer
}
