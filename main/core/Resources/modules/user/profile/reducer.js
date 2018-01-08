import {makeReducer} from '#/main/core/scaffolding/reducer'
import {makeFormReducer} from '#/main/core/data/form/reducer'
import {makePageReducer} from '#/main/core/layout/page/reducer'

import {
  PROFILE_FACET_OPEN
} from '#/main/core/user/profile/actions'

const reducer = makePageReducer({}, {
  currentFacet: makeReducer(null, {
    [PROFILE_FACET_OPEN]: (state, action) => action.id
  }),
  facets: makeReducer([], {}),
  user: makeFormReducer('user', {})
})

export {
  reducer
}
