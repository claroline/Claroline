import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors as dashboardSelectors} from '#/plugin/analytics/resource/dashboard/store/selectors'

const reducer = combineReducers({
  roles: makeListReducer(dashboardSelectors.STORE_NAME + '.requirements.roles', {}, {
    invalidated: makeReducer(false, {
      [RESOURCE_LOAD]: () => true
    })
  }),
  users: makeListReducer(dashboardSelectors.STORE_NAME + '.requirements.users', {}, {
    invalidated: makeReducer(false, {
      [RESOURCE_LOAD]: () => true
    })
  })
})

export {
  reducer
}
