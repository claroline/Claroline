import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {
  PROFILE_FACET_OPEN,
  PROFILE_LOAD,
  PROFILE_SET_LOADED
} from '#/main/community/profile/store/actions'

import {decorate} from '#/main/community/profile/decorator'
import {getDefaultFacet} from '#/main/community/profile/utils'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [PROFILE_LOAD]: () => true,
    [PROFILE_SET_LOADED]: (state, action) => action.loaded
  }),
  currentFacet: makeReducer(getDefaultFacet().id, {
    [PROFILE_FACET_OPEN]: (state, action) => action.id
  }),
  facets: makeReducer(decorate([]), {
    [PROFILE_LOAD]: (state, action) => decorate(action.facets || [])
  }),
  parameters: makeReducer({}, {
    [PROFILE_LOAD]: (state, action) => action.parameters || {}
  })
})

export {
  reducer
}
