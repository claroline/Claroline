/**
 * Resource explorer store.
 *
 * NB. It's not an auto mountable one, because it's used by
 * multiple implementations (ResourceManager, Directory, ResourcePicker) and
 * it can be mounted multiple times in the current app store (this is not
 * possible in auto mountable stores).
 */

import {actions} from '#/main/core/resource/explorer/store/actions'
import {makeResourceExplorerReducer} from '#/main/core/resource/explorer/store/reducer'
import {selectors} from '#/main/core/resource/explorer/store/selectors'

// export store module
export {
  actions,
  makeResourceExplorerReducer,
  selectors
}
