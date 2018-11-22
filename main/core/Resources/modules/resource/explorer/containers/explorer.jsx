import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {withRouter} from '#/main/app/router'

import {actions, selectors} from '#/main/core/resource/explorer/store'
import {ResourceExplorer as ResourceExplorerComponent} from '#/main/core/resource/explorer/components/explorer'

const ResourceExplorerContainer = props =>
  <ResourceExplorerComponent {...props} />

const ResourceExplorer = withRouter(connect(
  (state, ownProps) => ({
    root: selectors.root(selectors.explorer(state, ownProps.name)),
    currentId: selectors.currentId(selectors.explorer(state, ownProps.name)),
    directories: selectors.directories(selectors.explorer(state, ownProps.name)),
    listConfiguration: selectors.listConfiguration(selectors.explorer(state, ownProps.name)),
    showSummary: selectors.showSummary(selectors.explorer(state, ownProps.name)),
    openSummary: selectors.openSummary(selectors.explorer(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    changeDirectory(directoryId = null) {
      dispatch(actions.changeDirectory(ownProps.name, directoryId))
    },
    toggleDirectoryOpen(directory, opened) {
      dispatch(actions.toggleDirectoryOpen(ownProps.name, directory, opened))
    }
  })
)(ResourceExplorerContainer))

ResourceExplorer.propTypes = {
  name: T.string.isRequired
}

export {
  ResourceExplorer
}