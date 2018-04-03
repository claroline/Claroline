import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

const reducer = combineReducers({
  picker: makeListReducer('messages.picker'),
  list: makeListReducer('messages.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/messages.current']: () => true
    })
  }),
  current: makeFormReducer('messages.current', {}, {})
})

export {
  reducer
}