import {makeInstanceAction} from '#/main/app/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'

import {TOOL_LOAD, TOOL_OPEN} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/transfer/tools/export/store/selectors'
import {EXPORT_FILE_LOAD} from '#/main/transfer/tools/export/store/actions'

const reducer = combineReducers({
  explanation: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.explanation
  }),
  form: makeFormReducer(selectors.FORM_NAME, {new: true, data: {format: 'csv'}}),

  details: makeReducer(null, {
    [TOOL_OPEN]: () => null,
    [EXPORT_FILE_LOAD]: (state, action) => action.file
  }),
  list: makeListReducer(selectors.LIST_NAME, {
    sortBy: {property: 'createdAt', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [TOOL_OPEN]: () => true,
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.FORM_NAME)]: () => true
    })
  })
})

export {
  reducer
}
