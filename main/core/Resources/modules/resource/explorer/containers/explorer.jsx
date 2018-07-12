import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {makeCancelable} from '#/main/app/api'

import {actions, selectors} from '#/main/core/resource/explorer/store'
import {ResourceExplorer as ResourceExplorerComponent} from '#/main/core/resource/explorer/components/explorer'

class ResourceExplorerContainer extends Component {
  constructor(props) {
    super(props)

    if (this.props.initialized) {
      this.loadDirectories()
    }
  }

  componentDidUpdate(prevProps) {
    if (prevProps.initialized !== this.props.initialized && this.props.initialized) {
      this.loadDirectories()
    }
  }

  componentWillUnmount() {
    if (this.pending) {
      this.pending.cancel()
    }
  }

  loadDirectories() {
    if (!this.pending) {
      this.pending = makeCancelable(
        this.props.loadDirectories(this.props.root)
      )

      this.pending.promise.then(
        () => this.pending = null,
        () => this.pending = null
      )
    }
  }

  render() {
    return (
      <ResourceExplorerComponent {...this.props} />
    )
  }
}

const ResourceExplorer = connect(
  (state, ownProps) => ({
    initialized: selectors.initialized(selectors.explorer(state, ownProps.name)),
    root: selectors.root(selectors.explorer(state, ownProps.name)),
    current: selectors.current(selectors.explorer(state, ownProps.name)),
    directories: selectors.directories(selectors.explorer(state, ownProps.name))
  }),
  (dispatch, ownProps) => ({
    changeDirectory(directory) {
      dispatch(actions.openDirectory(ownProps.name, directory))
    },
    toggleDirectoryOpen(directory, opened) {
      dispatch(actions.toggleDirectoryOpen(ownProps.name, directory, opened))
    },
    loadDirectories(root = null) {
      if (root) {
        // mark directory has opened
        dispatch(actions.toggleDirectoryOpen(ownProps.name, root, true))
      } else {
        dispatch(actions.fetchDirectories(ownProps.name, root))
      }
    }
  })
)(ResourceExplorerContainer)

ResourceExplorer.propTypes = {
  name: T.string.isRequired
}

export {
  ResourceExplorer
}