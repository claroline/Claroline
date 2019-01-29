import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions as explorerActions, selectors as explorerSelectors} from '#/main/core/resource/explorer/store'
import {selectors} from '#/main/core/tools/resources/store'
import {ResourcesTool as ResourcesToolComponent} from '#/main/core/tools/resources/components/tool'

const ResourcesTool = withRouter(
  connect(
    (state) => ({
      loading: explorerSelectors.loading(explorerSelectors.explorer(state, selectors.STORE_NAME)),
      current: explorerSelectors.currentNode(explorerSelectors.explorer(state, selectors.STORE_NAME))
    }),
    (dispatch) => ({
      addNodes(resourceNodes) {
        dispatch(explorerActions.addNodes(selectors.STORE_NAME, resourceNodes))
      },

      updateNodes(resourceNodes) {
        dispatch(explorerActions.updateNodes(selectors.STORE_NAME, resourceNodes))
      },

      deleteNodes(resourceNodes) {
        dispatch(explorerActions.deleteNodes(selectors.STORE_NAME, resourceNodes))
      }
    }),
    undefined,
    {
      areStatesEqual: (next, prev) => selectors.store(prev) === selectors.store(next)
    }
  )(ResourcesToolComponent)
)

export {
  ResourcesTool
}
