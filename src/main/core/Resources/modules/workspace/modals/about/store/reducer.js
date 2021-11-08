import {makeReducer} from '#/main/app/store/reducer'

import {LOAD_WORKSPACE_ABOUT} from '#/main/core/workspace/modals/about/store/actions'

const reducer = makeReducer(null, {
  [LOAD_WORKSPACE_ABOUT]: (state, action) => action.workspace
})

export {
  reducer
}
