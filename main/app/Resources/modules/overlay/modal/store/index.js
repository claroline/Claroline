/**
 * Modal store.
 * Manages the display of modals.
 */

import {registry} from '#/main/app/store/registry'

import {actions}   from '#/main/app/overlay/modal/store/actions'
import {reducer}   from '#/main/app/overlay/modal/store/reducer'
import {selectors} from '#/main/app/overlay/modal/store/selectors'

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
