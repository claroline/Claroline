import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {WorkspaceForm} from '#/main/core/workspace/components/form'
import {WorkspaceList} from '#/main/core/workspace/components/list'

// TODO : redirect to public list if user is not registered

const WorkspacesTool = (props) =>
  <ToolPage
    actions={[
      {
        name: 'new',
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-plus',
        label: trans('create_workspace', {}, 'workspace'),
        target: `${props.path}/new`,
        primary: true,
        displayed: props.creatable
      }
    ]}
    subtitle={
      <Routes
        path={props.path}
        routes={[
          {path: '/new',        render: () => trans('new_workspace', {}, 'workspace'), disabled: !props.creatable},
          {path: '/registered', render: () => trans('my_workspaces', {}, 'workspace')},
          {path: '/public',     render: () => trans('public_workspaces', {}, 'workspace')},
          {path: '/managed',    render: () => trans('managed_workspaces', {}, 'workspace')},
          {path: '/model',      render: () => trans('workspace_models', {}, 'workspace'), disabled: !props.creatable}
        ]}
      />
    }
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/new',
          disabled: !props.creatable,
          render: () => {
            return (
              <WorkspaceForm
                name="workspaces.creation"
                buttons={true}
                cancel={{
                  type: LINK_BUTTON,
                  target: props.path,
                  exact: true
                }}
              />
            )
          }
        }, {
          path: '/registered',
          render: () => {
            return (
              <WorkspaceList
                url={['apiv2_workspace_list_registered']}
                name="workspaces.registered"
              />
            )
          }
        }, {
          path: '/public',
          render: () => {
            return (
              <WorkspaceList
                url={['apiv2_workspace_list_registerable']}
                name="workspaces.public"
              />
            )
          }
        }, {
          path: '/managed',
          render: () => {
            return (
              <WorkspaceList
                url={['apiv2_workspace_list_managed']}
                name="workspaces.managed"
              />
            )
          }
        }, {
          path: '/model',
          disabled: !props.creatable,
          render: () => {
            return (
              <WorkspaceList
                url={['apiv2_workspace_list_managed']}
                name="workspaces.models"
              />
            )
          }
        }, {
          path: '/:id',
          render: () => {
            return 'workspace open'
          }
        }
      ]}

      redirect={[
        {from: '/', exact: true, to: '/registered'}
      ]}
    />
  </ToolPage>

WorkspacesTool.propTypes = {
  path: T.string.isRequired,
  creatable: T.bool.isRequired,
  open: T.func.isRequired
}

export {
  WorkspacesTool
}
