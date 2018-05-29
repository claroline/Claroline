/**
 * Resource store.
 * Manages the state of individual resources.
 */

import {registry} from '#/main/app/store/registry'

import {actions}   from '#/main/core/resource/store/actions'
import {reducer}   from '#/main/core/resource/store/reducer'
import {selectors} from '#/main/core/resource/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, reducer)

// export store module
export {
  // action creators
  actions,
  // reducers
  reducer,
  // selectors
  selectors
}
