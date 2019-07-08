import {combineReducers, makeReducer} from '#/main/app/store/reducer'

import {makeListReducer} from '#/main/app/content/list/store'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {FORM_SUBMIT_SUCCESS} from '#/main/app/content/form/store/actions'
import {selectors as baseSelectors} from '#/main/core/administration/users/store'

const reducer = combineReducers({
  list: makeListReducer(baseSelectors.STORE_NAME+'.locations.list', {}, {
    invalidated: makeReducer(false, {
      [FORM_SUBMIT_SUCCESS+'/'+baseSelectors.STORE_NAME+'.locations.current']: () => true // todo : find better
    })
  }),
  current: makeFormReducer(baseSelectors.STORE_NAME+'.locations.current', {}, {
    users: makeListReducer(baseSelectors.STORE_NAME+'.locations.current.users'),
    organizations: makeListReducer(baseSelectors.STORE_NAME+'.locations.current.organizations'),
    groups: makeListReducer(baseSelectors.STORE_NAME+'.locations.current.groups')
  })
})

export {
  reducer
}
