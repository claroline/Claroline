import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isEmpty from 'lodash/isEmpty'

import {trans, number} from '#/main/app/intl'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON} from '#/main/app/buttons'
import {LiquidGauge} from '#/main/core/layout/gauge/components/liquid-gauge'

import {MenuMain} from '#/main/app/layout/menu/containers/main'
import {ToolMenu} from '#/main/core/tool/containers/menu'
import {route as toolRoute} from '#/main/core/tool/routing'
import {User as UserTypes} from '#/main/core/user/prop-types'
import {constants as baseConstants} from '#/main/core/constants'

import {route as workspaceRoute} from '#/main/core/workspace/routing'
import {getActions} from '#/main/core/workspace/utils'
import {Workspace as WorkspaceTypes, UserEvaluation as UserEvaluationTypes} from '#/main/core/workspace/prop-types'
import {constants} from '#/main/core/workspace/constants'

const WorkspaceShortcuts = props =>
  <Toolbar
    id="app-menu-shortcuts"
    className="app-menu-shortcuts"
    buttonName="btn btn-link"
    tooltip="bottom"
    actions={props.shortcuts}
    onClick={props.autoClose}
  />

const WorkspaceProgression = props => {
  let progression = 0
  if (props.userEvaluation.progression) {
    progression = props.userEvaluation.progression
    if (props.userEvaluation.progressionMax) {
      progression = (progression / props.userEvaluation.progressionMax) * 100
    }
  }

  return (
    <section className="app-menu-status">
      <h2 className="sr-only">
        {trans('my_progression')}
      </h2>

      <LiquidGauge
        id="workspace-progression"
        type="user"
        value={progression}
        displayValue={(value) => number(value) + '%'}
        width={70}
        height={70}
      />

      <div className="app-menu-status-info">
        <h3 className="h4">
          {!isEmpty(props.roles) ?
            props.roles.map(role => trans(role.translationKey)).join(', ') :
            trans('guest')
          }
        </h3>

        {constants.EVALUATION_STATUSES[get(props.userEvaluation, 'status', baseConstants.EVALUATION_STATUS_UNKNOWN)]}
      </div>
    </section>
  )
}

WorkspaceProgression.propTypes = {
  roles: T.arrayOf(T.shape({
    translationKey: T.string.isRequired
  })),
  userEvaluation: T.shape(
    UserEvaluationTypes.propTypes
  )
}

const WorkspaceMenu = (props) => {
  let workspaceActions
  if (!isEmpty(props.workspace)) {
    workspaceActions = getActions([props.workspace], {
      update(workspaces) {
        props.update(workspaces[0])
      },
      delete() {
        props.history.push(toolRoute('workspaces'))
      }
    }, props.basePath, props.currentUser)
  }

  let workspaceRoles = []
  if (!isEmpty(props.workspace) && props.currentUser) {
    workspaceRoles = props.currentUser.roles.filter(role => -1 !== role.name.indexOf(props.workspace.id))
  }

  return (
    <MenuMain
      title={!isEmpty(props.workspace) ? props.workspace.name : trans('workspace')}
      backAction={{
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-angle-double-left',
        label: trans('workspaces'),
        target: toolRoute('workspaces'),
        exact: true
      }}

      tools={props.tools.map(tool => ({
        name: tool.name,
        icon: tool.icon,
        path: workspaceRoute(props.workspace, tool.name)
      }))}
      actions={workspaceActions}
    >
      {get(props.workspace, 'display.showProgression') &&
        <WorkspaceProgression
          roles={workspaceRoles}
          userEvaluation={props.userEvaluation}
        />
      }

      {!isEmpty(props.shortcuts) &&
        <WorkspaceShortcuts
          shortcuts={workspaceActions.then(actions => {
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
                      target: workspaceRoute(props.workspace, tool.name)
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

WorkspaceMenu.propTypes = {
  history: T.shape({
    push: T.func.isRequired
  }).isRequired,
  basePath: T.string,
  section: T.string,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ),
  impersonated: T.bool.isRequired,
  currentUser: T.shape(
    UserTypes.propTypes
  ),
  userEvaluation: T.shape({

  }),
  shortcuts: T.arrayOf(T.shape({
    type: T.oneOf(['tool', 'action']).isRequired,
    name: T.string.isRequired
  })),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired
  })),
  changeSection: T.func.isRequired,
  update: T.func.isRequired
}

WorkspaceMenu.defaultProps = {
  workspace: {},
  shortcuts: []
}

export {
  WorkspaceMenu
}
