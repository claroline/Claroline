import {makeReducer, combineReducers} from '#/main/app/store/reducer'
import {makeFormReducer} from '#/main/app/content/form/store/reducer'

import {
  PROFILE_FACET_OPEN,
  PROFILE_LOAD,
  PROFILE_SET_LOADED
} from '#/main/core/user/profile/store/actions'

import {decorate} from '#/main/core/user/profile/decorator'
import {selectors} from '#/main/core/user/profile/store/selectors'

const reducer = combineReducers({
  loaded: makeReducer(false, {
    [PROFILE_LOAD]: () => true,
    [PROFILE_SET_LOADED]: (state, action) => action.loaded
  }),
  currentFacet: makeReducer(null, {
    [PROFILE_FACET_OPEN]: (state, action) => action.id
  }),
  facets: makeReducer([], {
    [PROFILE_LOAD]: (state, action) => decorate(action.facets || [])
  }),
  user: makeFormReducer(selectors.FORM_NAME, {}, {
    data: makeReducer({}, {
      [PROFILE_LOAD]: (state, action) => action.user
    }),
    originalData: makeReducer({}, {
      [PROFILE_LOAD]: (state, action) => action.user
    })
  }),
  parameters: makeReducer({}, {
    [PROFILE_LOAD]: (state, action) => action.parameters || {}
  })
})

export {
  reducer
}
