import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeInstanceAction} from '#/main/app/store/actions'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

import {TOOL_LOAD} from '#/main/core/tool/store/actions'
import {selectors} from '#/main/core/administration/connection-messages/store/selectors'

const reducer = combineReducers({
  list: makeListReducer(selectors.STORE_NAME+'.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+selectors.STORE_NAME+'.current']: () => true,
      [makeInstanceAction(TOOL_LOAD, selectors.STORE_NAME)]: () => true
    })
  }),
  current: makeFormReducer(selectors.STORE_NAME + '.current')
})

export {
  reducer
}
