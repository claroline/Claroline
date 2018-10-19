import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {ResourceExplorer} from '#/main/core/resource/explorer/containers/explorer'
import {getActions} from '#/main/core/resource/utils'

import {actions as explorerActions} from '#/main/core/resource/explorer/store'
import {selectors} from '#/main/core/resources/directory/player/store'

// TODO : fix reloading at resource creation

const DirectoryPlayerComponent = (props) =>
  <ResourceExplorer
    name={selectors.EXPLORER_NAME}
    primaryAction={(resourceNode) => ({ // todo : use resource default action
      type: URL_BUTTON,
      label: trans('open', {}, 'actions'),
      target: [ 'claro_resource_show', {
        type: resourceNode.meta.type,
        id: resourceNode.id
      }]
    })}
    actions={(resourceNodes) => getActions(resourceNodes, {
      add: props.addNodes,
      update: props.updateNodes,
      delete: props.deleteNodes
    }, true)}
  />

DirectoryPlayerComponent.propTypes = {
  addNodes: T.func.isRequired,
  updateNodes: T.func.isRequired,
  deleteNodes: T.func.isRequired
}

const DirectoryPlayer = connect(
  null,
  (dispatch) => ({
    addNodes(resourceNodes) {
      dispatch(explorerActions.addNodes(selectors.EXPLORER_NAME, resourceNodes))
    },

    updateNodes(resourceNodes) {
      dispatch(explorerActions.updateNodes(selectors.EXPLORER_NAME, resourceNodes))
    },

    deleteNodes(resourceNodes) {
      dispatch(explorerActions.deleteNodes(selectors.EXPLORER_NAME, resourceNodes))
    }
  })
)(DirectoryPlayerComponent)

export {
  DirectoryPlayer
}
