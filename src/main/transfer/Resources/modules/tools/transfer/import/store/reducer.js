import {makeInstanceAction} from '#/main/app/store/actions'
import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer, FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors as baseSelectors} from '#/main/transfer/tools/transfer/store/selectors'

import {selectors} from '#/main/transfer/tools/transfer/import/store/selectors'
import {IMPORT_FILE_LOAD} from '#/main/transfer/tools/transfer/import/store/actions'

const reducer = combineReducers({
  explanation: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: (state, action) => action.toolData.import.explanation
  }),
  samples: makeReducer({}, {
    [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: (state, action) => action.toolData.import.samples
  }),
  form: makeFormReducer(selectors.STORE_NAME+'.form'),

  details: makeReducer(null, {
    [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => null,
    [IMPORT_FILE_LOAD]: (state, action) => action.file
  }),
  list: makeListReducer(selectors.STORE_NAME+'.list', {
    sortBy: {property: 'createdAt', direction: -1}
  }, {
    invalidated: makeReducer(false, {
      [makeInstanceAction(TOOL_LOAD, baseSelectors.STORE_NAME)]: () => true,
      [makeInstanceAction(FORM_SUBMIT_SUCCESS, selectors.STORE_NAME+'.form')]: () => true
    })
  })
})

export {
  reducer
}
