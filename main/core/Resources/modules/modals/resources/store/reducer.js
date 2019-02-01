import {makeResourceExplorerReducer} from '#/main/core/resource/explorer/store'

import {selectors} from '#/main/core/modals/resources/store/selectors'

const reducer = makeResourceExplorerReducer(selectors.STORE_NAME)

export {
  reducer
}
