import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

const reducer = combineReducers({
  picker: makeListReducer('organizations.picker'),
  list: makeListReducer('organizations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/organizations.current']: () => true // todo : find better
    })
  }, {
    sortable: false,
    paginated: false
  }),
  current: makeFormReducer('organizations.current', {}, {
    workspaces: makeListReducer('organizations.current.workspaces'),
    users: makeListReducer('organizations.current.users'),
    groups: makeListReducer('organizations.current.groups'),
    managers: makeListReducer('organizations.current.managers')
  })
})

export {
  reducer
}
