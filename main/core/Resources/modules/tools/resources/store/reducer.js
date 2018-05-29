import {makeReducer} from '#/main/app/store/reducer'
import {makeListReducer} from '#/main/core/data/list/reducer'

import {DIRECTORY_CHANGE} from '#/main/core/tools/resources/store/actions'

export const reducer = {
  root: makeReducer(null),
  current: makeReducer(null, {
    [DIRECTORY_CHANGE]: (state, action) => action.directoryNode
  }),
  resources: makeListReducer('resources')
}
