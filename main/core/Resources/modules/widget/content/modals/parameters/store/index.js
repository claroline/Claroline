/**
 * Widget parameters store.
 */

import {registry} from '#/main/app/store/registry'

import {reducer} from '#/main/core/widget/content/modals/parameters/store/reducer'
import {selectors} from '#/main/core/widget/content/modals/parameters/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, reducer)

// export store module
export {
  selectors
}
