/* global window */

import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {url} from '#/main/app/api'
import {Toolbar} from '#/main/app/overlay/toolbar/components/toolbar'
import {ASYNC_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {hasPermission} from '#/main/core/workspace/permissions'
import {select} from '#/main/core/workspace/selectors'

import {MODAL_WORKSPACE_ABOUT} from '#/main/core/workspace/modals/about'
import {MODAL_WORKSPACE_IMPERSONATION} from '#/main/core/workspace/modals/impersonation'
import {MODAL_WORKSPACE_PARAMETERS} from '#/main/core/workspace/modals/parameters'

const WorkspaceToolbarComponent = props =>
  <Toolbar
    active={props.openedTool}
    primary={props.tools[0]}
    tools={props.tools.slice(1)}
    actions={[
      {
        type: MODAL_BUTTON,
        icon: 'fa fa-info',
        label: trans('show-info', {}, 'actions'),
        displayed: hasPermission('open', props.workspace),
        modal: [MODAL_WORKSPACE_ABOUT, {
          workspace: props.workspace
        }]
      }, {
        type: MODAL_BUTTON,
        icon: 'fa fa-cog',
        label: trans('configure', {}, 'actions'),
        displayed: hasPermission('administrate', props.workspace),
        modal: [MODAL_WORKSPACE_PARAMETERS, {
          workspace: props.workspace
        }]
      }, {
        type: MODAL_BUTTON,
        icon: 'fa fa-user-secret',
        label: trans('view-as', {}, 'actions'),
        displayed: hasPermission('administrate', props.workspace),
        modal: [MODAL_WORKSPACE_IMPERSONATION, {
          workspace: props.workspace
        }]
      }, {
        type: URL_BUTTON,
        icon: 'fa fa-download',
        label: trans('export', {}, 'actions'),
        //displayed: hasPermission('export', props.workspace),
        displayed: false, //currently broken
        target: ['claro_workspace_export', {workspace: props.workspace.id}]
      }, {
        type: ASYNC_BUTTON,
        icon: 'fa fa-trash-o',
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
    ]}
  />

WorkspaceToolbarComponent.propTypes = {
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  openedTool: T.string,
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    open: T.oneOfType([T.array, T.string])
  }))
}

// todo : remove the container when the toolbar will be moved in the main app
// (that's why it's in the components folder)
const WorkspaceToolbar = connect(
  (state) => ({
    workspace: select.workspace(state),
    tools: select.tools(state),
    openedTool: select.openedTool(state)
  })
)(WorkspaceToolbarComponent)

export {
  WorkspaceToolbar
}
