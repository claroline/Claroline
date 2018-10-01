/* global window */

import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'
import {Toolbar} from '#/main/app/overlay/toolbar/components/toolbar'
import {ASYNC_BUTTON, CALLBACK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {actions as walkthroughActions} from '#/main/app/overlay/walkthrough/store'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {hasPermission} from '#/main/core/workspace/permissions'
import {select} from '#/main/core/workspace/selectors'

import {MODAL_WORKSPACE_ABOUT} from '#/main/core/workspace/modals/about'
import {MODAL_WORKSPACE_IMPERSONATION} from '#/main/core/workspace/modals/impersonation'
import {MODAL_WORKSPACE_PARAMETERS} from '#/main/core/workspace/modals/parameters'

class WorkspaceToolbarComponent extends Component {
  constructor(props) {
    super(props)

    this.state = {
      openedTool: props.tools.find(tool => props.openedTool === tool.name),
      actions: [
        {
          name: 'walkthrough',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-street-view',
          label: trans('show-walkthrough', {}, 'actions'),
          callback: () => this.startWalkthrough()
        }, {
          name: 'about',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-info',
          label: trans('show-info', {}, 'actions'),
          displayed: hasPermission('open', props.workspace),
          modal: [MODAL_WORKSPACE_ABOUT, {
            workspace: props.workspace
          }]
        }, {
          name: 'parameters',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-cog',
          label: trans('configure', {}, 'actions'),
          displayed: hasPermission('administrate', props.workspace),
          modal: [MODAL_WORKSPACE_PARAMETERS, {
            workspace: props.workspace
          }]
        }, {
          name: 'impersonation',
          type: MODAL_BUTTON,
          icon: 'fa fa-fw fa-user-secret',
          label: trans('view-as', {}, 'actions'),
          displayed: hasPermission('administrate', props.workspace),
          modal: [MODAL_WORKSPACE_IMPERSONATION, {
            workspace: props.workspace
          }]
        }, {
          name: 'export',
          type: URL_BUTTON,
          icon: 'fa fa-fw fa-download',
          label: trans('export', {}, 'actions'),
          //displayed: hasPermission('export', props.workspace),
          displayed: false, //currently broken
          target: ['claro_workspace_export', {workspace: props.workspace.id}]
        }, {
          name: 'delete',
          type: ASYNC_BUTTON,
          icon: 'fa fa-fw fa-trash-o',
          label: trans('delete', {}, 'actions'),
          displayed: hasPermission('delete', props.workspace),
          request: {
            type: 'delete',
            url: ['apiv2_workspace_delete_bulk', {ids: [props.workspace.id]}],
            request: {
              method: 'DELETE'
            },
            success: () => window.location = url(['claro_desktop_open'])
          },
          dangerous: true,
          confirm: {
            title: trans('workspace_delete_confirm_title'),
            subtitle: props.workspace.name,
            message: trans('workspace_delete_confirm_message')
          }
        }
      ]
    }
  }

  startWalkthrough() {
    this.props.startWalkthrough([
      {
        highlight: ['.workspace-toolbar-container'],
        content: {
          title: trans('workspace.sidebar.intro.title', {}, 'walkthrough'),
          message: trans('workspace.sidebar.intro.message', {}, 'walkthrough')
        },
        position: {
          target: '.workspace-toolbar-container',
          placement: 'right'
        }
      }, {
        highlight: ['.tools'],
        content: {
          title: trans('workspace_tools', {}, 'walkthrough'),
          message: trans('workspace.sidebar.tools_group.message', {}, 'walkthrough')
        },
        position: {
          target: '.tools',
          placement: 'right'
        }
      }
    ].concat(
      // help for active tool
      this.state.openedTool ? [{
        highlight: [`#tool-link-${this.state.openedTool.name}`],
        content: {
          message: trans('workspace.sidebar.opened_tool.message', {}, 'walkthrough')
        },
        position: {
          target: `#tool-link-${this.state.openedTool.name}`,
          placement: 'right'
        }
      }] : [],
      // help for each tool
      this.props.tools.map(tool => ({
        highlight: [`#tool-link-${tool.name}`],
        content: {
          icon: `fa fa-${tool.icon}`,
          title: trans('tool', {toolName: trans(tool.name, {}, 'tools')}, 'walkthrough'),
          message: trans(`workspace.tools.${tool.name}.message`, {}, 'walkthrough'),
          link: trans(`workspace.tools.${tool.name}.documentation`, {}, 'walkthrough')
        },
        position: {
          target: `#tool-link-${tool.name}`,
          placement: 'right'
        }
      })),
      // help for action group
      [{
        highlight: ['.additional-tools'],
        content: {
          title: trans('actions', {}, 'walkthrough'),
          message: trans('workspace.sidebar.actions_group.message', {}, 'walkthrough')
        },
        position: {
          target: '.additional-tools',
          placement: 'right'
        }
      }],
      // help for each displayed action
      this.state.actions
        .filter(action => undefined === action.displayed || action.displayed)
        .map(action => ({
          highlight: [`#action-link-${action.name}`],
          content: {
            icon: action.icon,
            title: trans('action', {actionName: action.label}, 'walkthrough'),
            message: trans(`workspace.actions.${action.name}`, {}, 'walkthrough')
          },
          position: {
            target: `#action-link-${action.name}`,
            placement: 'right'
          }
        }))
    ))
  }

  render() {
    return (
      <Toolbar
        active={this.props.openedTool}
        tools={this.props.tools}
        actions={this.state.actions}
      />
    )
  }
}

WorkspaceToolbarComponent.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  openedTool: T.string,
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    open: T.oneOfType([T.array, T.string])
  })),
  startWalkthrough: T.func.isRequired
}

// todo : remove the container when the toolbar will be moved in the main app
// (that's why it's in the components folder)
const WorkspaceToolbar = connect(
  (state) => ({
    workspace: select.workspace(state),
    tools: select.tools(state),
    openedTool: select.openedTool(state)
  }),
  (dispatch) => ({
    startWalkthrough(steps) {
      dispatch(walkthroughActions.start(steps))
    }
  })
)(WorkspaceToolbarComponent)

export {
  WorkspaceToolbar
}
