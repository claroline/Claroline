import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {LIST_DATA_DELETE} from '#/main/app/content/list/store/actions'

const reducer = combineReducers({
  list: makeListReducer('sessions.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/sessions.current']: () => true,
      [LIST_DATA_DELETE+'/courses.current.sessions']: () => true
    })
  }),
  current: makeFormReducer('sessions.current', {}, {
    users: makeListReducer('sessions.current.users'),
    events: makeListReducer('sessions.current.events', {}, {
      invalidated: makeReducer(false, {
        [FORM_SUBMIT_SUCCESS+'/events.current']: () => true
      })
    })
  }),
  picker: makeListReducer('sessions.picker')
})

export {
  reducer
}
