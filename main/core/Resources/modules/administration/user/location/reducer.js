import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'

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
