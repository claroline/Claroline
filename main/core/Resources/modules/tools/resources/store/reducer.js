import {makeResourceExplorerReducer} from '#/main/core/resource/explorer/store'

import {selectors} from '#/main/core/tools/resources/store/selectors'

const reducer = {
  [selectors.STORE_NAME]: makeResourceExplorerReducer(selectors.STORE_NAME)
}

export {
  reducer
}
