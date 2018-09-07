import {combineReducers, makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  list: makeListReducer('cursus.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/cursus.current']: () => true
    })
  }),
  current: makeFormReducer('cursus.current', {}, {
    users: makeListReducer('cursus.current.users'),
    organizations: combineReducers({
      list: makeListReducer('cursus.current.organizations.list'),
      picker: makeListReducer('cursus.current.organizations.picker')
    })
  })
})

export {
  reducer
}
