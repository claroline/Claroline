/**
 * Resource rights store.
 */

import {registry} from '#/main/app/store/registry'

import {reducer} from '#/main/core/resource/modals/rights/store/reducer'
import {selectors} from '#/main/core/resource/modals/rights/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, reducer)

// export store module
export {
  selectors
}
