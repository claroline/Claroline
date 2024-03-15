import React from 'react'
import {PropTypes as T} from 'prop-types'

import {Toolbar} from '#/main/app/action'
import {getActions} from '#/main/core/resource/utils'
import {PageMenu} from '#/main/app/page/components/menu'
import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {route} from '#/main/core/resource/routing'
import {ResourceNode as ResourceNodeTypes} from '#/main/core/resource/prop-types'
import {LINK_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const ResourceMenu = (props) =>
  <PageMenu
    actions={[
      {
        name: 'overview',
        type: LINK_BUTTON,
        //icon: 'fa fa-fw fa-home',
        //label: trans('home'),
        label: trans('resource_overview', {}, 'resource'),
        target: props.path,
        displayed: props.overview,
        exact: true
      }
    ].concat(props.actions)}
  >
    <Toolbar
      className="nav nav-underline"
      buttonName="nav-link"
      toolbar="edit more"
      tooltip="bottom"
      actions={getActions([props.resourceNode], {
        add: () => {
          props.reload()
        },
        update: (resourceNodes) => {
          // checks if the action have modified the current node
          const currentNode = resourceNodes.find(node => node.id === props.resourceNode.id)
          if (currentNode) {
            // grabs updated data
            props.reload()
          }
        },
        delete: (resourceNodes) => {
          // checks if the action have deleted the current node
          const currentNode = resourceNodes.find(node => node.id === props.resourceNode.id)
          if (currentNode) {
            let redirect
            if (currentNode.parent) {
              redirect = route(currentNode.parent)
            } else {
              redirect = workspaceRoute(currentNode.workspace, 'resources')
            }

            props.history.push(redirect)
          }
        }
      }, props.basePath, props.currentUser, false)}
    />
  </PageMenu>

ResourceMenu.propTypes = {
  overview: T.bool,

  // from resource
  path: T.string.isRequired,
  basePath: T.string.isRequired,
  resourceNode: T.shape(
    ResourceNodeTypes.propTypes
  ).isRequired,
  reload: T.func.isRequired,

  // from security
  currentUser: T.object,

  // from router
  history: T.shape({
    push: T.func.isRequired
  })
}

ResourceMenu.defaultProps = {
  overview: false,
  actions: []
}

export {
  ResourceMenu
}
