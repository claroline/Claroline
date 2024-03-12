import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {hasPermission} from '#/main/app/security/permissions'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {ContextUser} from '#/main/app/context/containers/user'

const ContextMenu = (props) =>
  <MenuMain
    title={props.title}

    tools={props.tools
      .filter(tool => hasPermission('open', tool))
      .map(tool => ({
        name: tool.name,
        icon: tool.icon,
        path: props.basePath + '/' + tool.name,
        order: get(tool, 'display.order'),
        displayed: !get(tool, 'restrictions.hidden', false)
      }))
    }
    actions={props.actions}
    thumbnail={props.thumbnail}
  >
    <ContextUser title={props.title} />

    {props.children}
  </MenuMain>

ContextMenu.propTypes = {
  basePath: T.string,
  title: T.string.isRequired,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  })),
  children: T.node
}

ContextMenu.defaultProps = {
  basePath: '',
  actions: []
}

export {
  ContextMenu
}
