/**
 * Tool store.
 * Manages the tool information & config.
 */

import {registry} from '#/main/app/store/registry'

import {reducer} from '#/main/core/tool/store/reducer'
import {selectors} from '#/main/core/tool/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, reducer)

// export store module
export {
  // action creators

  // reducers
  reducer,
  // selectors
  selectors
}
