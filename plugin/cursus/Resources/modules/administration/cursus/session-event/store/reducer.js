import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {LIST_DATA_DELETE} from '#/main/app/content/list/store/actions'

const reducer = combineReducers({
  list: makeListReducer('events.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/events.current']: () => true,
      [LIST_DATA_DELETE+'/sessions.current.events']: () => true
    })
  }),
  current: makeFormReducer('events.current', {}, {
    users: makeListReducer('events.current.users')
  })
})

export {
  reducer
}
