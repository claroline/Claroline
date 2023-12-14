import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isArray from 'lodash/isArray'
import isEmpty from 'lodash/isEmpty'

import {hasPermission} from '#/main/app/security/permissions'
import {trans} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {ToolMenu} from '#/main/core/tool/containers/menu'

const ContextShortcuts = props =>
  <Toolbar
    id="app-menu-shortcuts"
    name="app-menu-shortcuts"
    buttonName="btn"
    tooltip="bottom"
    actions={props.shortcuts}
    onClick={props.autoClose}
  />

const ContextMenu = (props) => {
  let actionPromise
  if (props.actions && isArray(props.actions)) {
    actionPromise = Promise.resolve(props.actions)
  } else {
    actionPromise = props.actions
  }

  return (
    <MenuMain
      title={props.title}
      backAction={props.backAction}

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
    >
      {props.children}

      {!isEmpty(props.shortcuts) &&
        <ContextShortcuts
          shortcuts={actionPromise.then(actions => {
            return props.shortcuts
              .map(shortcut => {
                if ('tool' === shortcut.type) {
                  const tool = props.tools.find(tool => tool.name === shortcut.name)
                  if (tool) {
                    return {
                      name: tool.name,
                      type: LINK_BUTTON,
                      icon: `fa fa-fw fa-${tool.icon}`,
                      label: trans('open-tool', {tool: trans(tool.name, {}, 'tools')}, 'actions'),
                      target: props.basePath + '/' + tool.name
                    }
                  }

                } else {
                  return actions.find(action => action.name === shortcut.name)
                }
              })
              .filter(link => !!link)
          })}
        />
      }

      <ToolMenu
        opened={'tool' === props.section}
        toggle={() => props.changeSection('tool')}
      />
    </MenuMain>
  )
}

ContextMenu.propTypes = {
  basePath: T.string,
  title: T.string.isRequired,
  backAction: T.shape(ActionTypes.propTypes),
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

  section: T.string,

  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    permissions: T.object
  })),
  children: T.node,
  changeSection: T.func.isRequired
}

ContextMenu.defaultProps = {
  basePath: '',
  actions: [],
  shortcuts: []
}

export {
  ContextMenu
}
