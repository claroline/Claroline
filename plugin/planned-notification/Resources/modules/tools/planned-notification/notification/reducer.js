import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

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