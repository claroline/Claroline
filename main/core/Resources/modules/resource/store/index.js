/**
 * Resource store.
 * Manages the state of individual resources.
 */

import {actions, RESOURCE_LOAD}   from '#/main/core/resource/store/actions'
import {reducer}   from '#/main/core/resource/store/reducer'
import {selectors} from '#/main/core/resource/store/selectors'

// export store module
export {
  // public actions
  RESOURCE_LOAD,
  actions,
  reducer,
  selectors
}
