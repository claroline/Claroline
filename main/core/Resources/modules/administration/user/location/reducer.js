import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_RESET, FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

const reducer = combineReducers({
  list: makeListReducer('locations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/locations.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('locations.current', {}, {
    users: makeListReducer('locations.current.users', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/locations.current']: () => true // todo : find better
      })
    }),
    organizations: makeListReducer('locations.current.organizations', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/locations.current']: () => true // todo : find better
      })
    }),
    groups: makeListReducer('locations.current.groups', {}, {
      invalidated: makeReducer(false, {
        [FORM_RESET+'/locations.current']: () => true // todo : find better
      })
    })
  })
})

export {
  reducer
}
