import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions, selectors} from '#/main/core/modals/resources/store'
import {ResourceExplorer as ResourceExplorerComponent} from '#/main/core/modals/resources/components/explorer'

// NB. the `withRouter` HOC is required for when the Explorer is embedded in a modal
const ResourceExplorer = withRouter(
  connect(
    (state) => ({
      root: selectors.root(state),
      currentId: selectors.currentId(state),
      listConfiguration: selectors.listConfiguration(state)
    }),
    (dispatch) => ({
      changeDirectory(directoryId = null) {
        dispatch(actions.changeDirectory(directoryId))
      }
    })
  )(ResourceExplorerComponent)
)

export {
  ResourceExplorer
}