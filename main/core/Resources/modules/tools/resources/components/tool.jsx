import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {URL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceExplorer} from '#/main/core/resource/explorer/containers/explorer'
import {getActions, getToolbar} from '#/main/core/resource/utils'

import {actions as explorerActions, selectors as explorerSelectors} from '#/main/core/resource/explorer/store'
import {selectors} from '#/main/core/tools/resources/store'

const ResourcesToolComponent = props =>
  <ToolPage
    subtitle={props.current && props.current.name}
    path={props.current ? props.current.path.map(ancestorNode => ({
      label: ancestorNode.name,
      target: ['claro_resource_show_short', {id: ancestorNode.id}]
    })) : []}
    toolbar={getToolbar('add')}
    actions={props.current && getActions([props.current], {
      add: props.addNodes,
      update: props.updateNodes,
      delete: props.deleteNodes
    }, true)}
  >
    <ResourceExplorer
      name={selectors.STORE_NAME}
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
  </ToolPage>

ResourcesToolComponent.propTypes = {
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  addNodes: T.func.isRequired,
  updateNodes: T.func.isRequired,
  deleteNodes: T.func.isRequired
}

const ResourcesTool = connect(
  state => ({
    current: explorerSelectors.currentNode(explorerSelectors.explorer(state, selectors.STORE_NAME))
  }),
  dispatch => ({
    addNodes(resourceNodes) {
      dispatch(explorerActions.addNodes(selectors.STORE_NAME, resourceNodes))
    },

    updateNodes(resourceNodes) {
      dispatch(explorerActions.updateNodes(selectors.STORE_NAME, resourceNodes))
    },

    deleteNodes(resourceNodes) {
      dispatch(explorerActions.deleteNodes(selectors.STORE_NAME, resourceNodes))
    }
  })
)(ResourcesToolComponent)

export {
  ResourcesTool
}
