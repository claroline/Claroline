import {makeReducer, combineReducers} from '#/main/app/store/reducer'

import {SECURITY_USER_CHANGE} from '#/main/app/security/store/actions'
import {
  RESOURCE_OPEN,
  RESOURCE_LOAD,
  RESOURCE_SET_LOADED,
  RESOURCE_EVALUATION_UPDATE,
  RESOURCE_SET_ERROR
} from '#/main/core/resource/store/actions'

const reducer = combineReducers({
  slug: makeReducer(null, {
    [RESOURCE_OPEN]: (state, action) => action.resourceSlug
  }),
  loaded: makeReducer(false, {
    [SECURITY_USER_CHANGE]: () => false,
    [RESOURCE_OPEN]: () => false,
    [RESOURCE_SET_LOADED]: (state, action) => action.loaded,
    [RESOURCE_LOAD]: () => true,
    [RESOURCE_SET_ERROR]: () => true
  }),

  embedded: makeReducer(false, {
    [RESOURCE_OPEN]: (state, action) => action.embedded
  }),

  showHeader: makeReducer(true),

  /**
   * Manages the ResourceNode of the resource.
   */
  resourceNode: makeReducer({}, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.resourceNode
  }),

  /**
   * Manages the custom AbstractResource of the resource.
   */
  resource: makeReducer(null, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.resource || null
  }),

  /**
   * Manages current user's evaluation for the resource.
   */
  userEvaluation: makeReducer(null, {
    [RESOURCE_OPEN]: () => null,
    [RESOURCE_LOAD]: (state, action) => action.resourceData.userEvaluation || state,
    [RESOURCE_EVALUATION_UPDATE]: (state, action) => action.userEvaluation
  }),

  lifecycle: makeReducer({}),

  error: makeReducer(null, {
    [RESOURCE_LOAD]: (state, action) => action.resourceData.error || null,
    [RESOURCE_SET_ERROR]: (state, action) => ({
      code: action.code.toUpperCase(),
      message: action.message,
      additional: action.additional
    })
  })
})

export {
  reducer
}
