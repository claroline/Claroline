/**
 * Resource creation store.
 */

import {registry} from '#/main/app/store/registry'

import {actions}   from '#/main/core/resource/modals/creation/store/actions'
import {reducer}   from '#/main/core/resource/modals/creation/store/reducer'
import {selectors} from '#/main/core/resource/modals/creation/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, reducer)

// export store module
export {
  actions,
  selectors
}
