import {makeReducer} from '#/main/app/store/reducer'
import {makeResourceExplorerReducer} from '#/main/core/resource/explorer/store/reducer'

import {RESOURCE_LOAD} from '#/main/core/resource/store/actions'

import {selectors} from '#/main/core/resources/directory/player/store/selectors'

const reducer = {
  directoryExplorer: makeResourceExplorerReducer(selectors.EXPLORER_NAME, {}, {
    root: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.resourceNode
    }),
    currentId: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.resourceNode.id
    }),
    currentNode: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.resourceNode
    }),
    currentConfiguration: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => action.resourceData.directory
    }),
    directories: makeReducer({}, {
      [RESOURCE_LOAD]: (state, action) => [action.resourceData.resourceNode]
    })
  })
}

export {
  reducer
}