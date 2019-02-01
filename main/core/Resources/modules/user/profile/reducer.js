import {makeReducer} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'
import {makeListReducer} from '#/main/app/content/list/store'
import {combineReducers} from '#/main/app/store/reducer'

import {
  PROFILE_FACET_OPEN
} from '#/main/core/user/profile/actions'

const reducer = {
  currentFacet: makeReducer(null, {
    [PROFILE_FACET_OPEN]: (state, action) => action.id
  }),
  facets: makeReducer([], {}),
  user: makeFormReducer('user', {}),
  parameters: makeReducer({}, {}),
  badges: combineReducers({
    mine: makeListReducer('badges.mine', {})
  })
}

export {
  reducer
}
