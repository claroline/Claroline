/**
 * Workspace parameters store.
 */

import {registry} from '#/main/app/store/registry'
import {makeFormReducer} from '#/main/core/data/form/reducer'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {selectors} from '#/main/core/workspace/modals/parameters/store/selectors'

// append the reducer to the store
registry.add(selectors.STORE_NAME, makeFormReducer(selectors.STORE_NAME, {
  data: WorkspaceTypes.defaultProps
}))

// export store module
export {
  // selectors
  selectors
}
