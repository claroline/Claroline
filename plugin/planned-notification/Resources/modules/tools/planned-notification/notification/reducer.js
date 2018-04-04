import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

const reducer = combineReducers({
  rolesPicker: makeListReducer('notifications.rolesPicker'),
  list: makeListReducer('notifications.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/notifications.current']: () => true
    })
  }),
  current: makeFormReducer('notifications.current', {}, {})
})

export {
  reducer
}