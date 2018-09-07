import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  picker: makeListReducer('messages.picker'),
  list: makeListReducer('messages.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/messages.current']: () => true
    })
  }),
  current: makeFormReducer('messages.current'),
  userspicker: makeListReducer('messages.userspicker')
})

export {
  reducer
}