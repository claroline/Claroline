import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {LINK_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {constants as toolConst} from '#/main/core/tool/constants'

import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {ResourceExplorer} from '#/main/core/resource/explorer/containers/explorer'
import {getActions, getToolbar} from '#/main/core/resource/utils'

import {selectors} from '#/main/core/tools/resources/store'

const ResourcesTool = props => {
  // remove workspace root from path (it's already known by the breadcrumb)
  // find a better way to handle this
  let ancestors = []
  if (props.current) {
    if (toolConst.TOOL_WORKSPACE === props.contextType && props.current.workspace) {
      ancestors = props.current.path.slice(1)
    } else {
      ancestors = props.current.path.slice(0)
    }
  }

  return (
    <ToolPage
      subtitle={props.current && props.current.name}
      path={ancestors.map(ancestorNode => ({
        type: LINK_BUTTON,
        label: ancestorNode.name,
        target: `/${ancestorNode.id}`
      }))}
      disabled={props.loading}
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
          target: ['claro_resource_show', {
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
  )
}

ResourcesTool.propTypes = {
  contextType: T.string.isRequired,
  current: T.shape(
    ResourceNodeTypes.propTypes
  ),
  loading: T.bool.isRequired,
  addNodes: T.func.isRequired,
  updateNodes: T.func.isRequired,
  deleteNodes: T.func.isRequired
}

export {
  ResourcesTool
}
