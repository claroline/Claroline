import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {WorkspaceList} from '#/main/core/workspace/components/list'
import {WorkspaceCreation} from '#/main/core/tools/workspaces/containers/creation'

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
          component: WorkspaceCreation
        }, {
          path: '/registered',
          render: () => {
            const Registered = (
              <WorkspaceList
                url={['apiv2_workspace_list_registered']}
                name="workspaces.registered"
              />
            )

            return Registered
          }
        }, {
          path: '/public',
          render: () => {
            const PublicList = (
              <WorkspaceList
                url={['apiv2_workspace_list_registerable']}
                name="workspaces.public"
              />
            )

            return PublicList
          }
        }, {
          path: '/managed',
          render: () => {
            const ManagedList = (
              <WorkspaceList
                url={['apiv2_workspace_list_managed']}
                name="workspaces.managed"
              />
            )

            return ManagedList
          }
        }, {
          path: '/model',
          disabled: !props.creatable,
          render: () => {
            const ModelList = (
              <WorkspaceList
                url={['apiv2_workspace_list_model']}
                name="workspaces.models"
              />
            )

            return ModelList
          }
        }
      ]}

      redirect={[
        {from: '/', exact: true, to: '/registered', disabled: !props.authenticated},
        {from: '/', exact: true, to: '/public', disabled: props.authenticated}
      ]}
    />
  </ToolPage>

WorkspacesTool.propTypes = {
  path: T.string.isRequired,
  authenticated: T.bool.isRequired,
  creatable: T.bool.isRequired
}

export {
  WorkspacesTool
}
