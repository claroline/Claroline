/**
 * Resource explorer store.
 */

import {registry} from '#/main/app/store/registry'

import {makeResourceExplorerReducer} from '#/main/core/resource/explorer/store'
import {selectors} from '#/main/core/resource/modals/explorer/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, makeResourceExplorerReducer(selectors.STORE_NAME))

// export store module
export {
  selectors
}
