import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'
import {actions, selectors} from '#/main/core/resource/explorer/store'
import {ResourceExplorer as ResourceExplorerComponent} from '#/main/core/resource/explorer/components/explorer'

// NB. the `withRouter` HOC is required for when the Explorer is embedded in a modal
const ResourceExplorer = withRouter(
  connect(
    (state, ownProps) => ({
      root: selectors.root(selectors.explorer(state, ownProps.name)),
      currentId: selectors.currentId(selectors.explorer(state, ownProps.name)),
      listConfiguration: selectors.listConfiguration(selectors.explorer(state, ownProps.name))
    }),
    (dispatch, ownProps) => ({
      changeDirectory(directoryId = null) {
        dispatch(actions.changeDirectory(ownProps.name, directoryId))
      }
    })
  )(ResourceExplorerComponent)
)

ResourceExplorer.propTypes = {
  name: T.string.isRequired
}

export {
  ResourceExplorer
}