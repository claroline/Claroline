import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

import {FORM_RESET, FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

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
    workspaces: makeListReducer('organizations.current.workspaces', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/organizations.current']: () => true // todo : find better
      })
    }),
    users: makeListReducer('organizations.current.users', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/organizations.current']: () => true // todo : find better
      })
    }),
    groups: makeListReducer('organizations.current.groups', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/organizations.current']: () => true // todo : find better
      })
    }),
    managers: makeListReducer('organizations.current.managers', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/organizations.current']: () => true // todo : find better
      })
    })
  })
})

export {
  reducer
}
