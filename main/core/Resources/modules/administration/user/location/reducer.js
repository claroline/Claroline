import {combineReducers, makeReducer} from '#/main/core/scaffolding/reducer'

import {makeListReducer} from '#/main/core/data/list/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/core/data/form/actions'

const reducer = combineReducers({
  list: makeListReducer('locations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/locations.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer('locations.current', {}, {
    users: makeListReducer('locations.current.users'),
    organizations: makeListReducer('locations.current.organizations'),
    groups: makeListReducer('locations.current.groups')
  })
})

export {
  reducer
}
