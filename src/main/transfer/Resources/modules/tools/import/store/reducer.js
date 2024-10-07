import {makeInstanceAction} from '#/main/app/store/actions'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'

import {TOOL_LOAD, TOOL_OPEN} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/transfer/tools/import/store/selectors'
import {reducer as logReducer} from '#/main/transfer/log/store/reducer'
import {IMPORT_FILE_LOAD} from '#/main/transfer/tools/import/store/actions'

const reducer = combineReducers({
  log: logReducer,
  explanation: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.explanation
  }),
  samples: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.samples
  }),
  form: makeFormReducer(selectors.FORM_NAME, {new: true, data: {format: 'csv'}}),

  details: makeReducer(null, {
    [TOOL_OPEN]: () => null,
    [IMPORT_FILE_LOAD]: (state, action) => action.file
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
