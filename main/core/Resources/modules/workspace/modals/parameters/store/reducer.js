import {makeFormReducer} from '#/main/core/data/form/reducer'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {selectors} from '#/main/core/workspace/modals/parameters/store/selectors'

const reducer = makeFormReducer(selectors.STORE_NAME, {
  data: WorkspaceTypes.defaultProps
})

export {
  reducer
}
