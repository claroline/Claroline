import {TOOL_LOAD} from '#/main/core/tool/store'

import {makeInstanceAction} from '#/main/app/store/actions'
import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {selectors} from '#/main/authentication/administration/authentication/store/selectors'
import {makeListReducer} from '#/main/app/content/list/store'

export const reducer = combineReducers({
  form: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.authentication
    }),
    originalData: makeReducer({}, {
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: (state, action) => action.toolData.authentication
    })
  }),
  ips: makeListReducer(selectors.STORE_NAME+'.ips'),
  tokens: makeListReducer(selectors.STORE_NAME+'.tokens')
})
