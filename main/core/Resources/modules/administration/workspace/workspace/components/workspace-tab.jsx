import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/core/router'
import {Workspace,  WorkspaceActions}  from '#/main/core/administration/workspace/workspace/components/workspace.jsx'
import {Workspaces, WorkspacesActions} from '#/main/core/administration/workspace/workspace/components/workspaces.jsx'

import {actions} from '#/main/core/administration/workspace/workspace/actions'

const WorkspaceTabActions = () =>
  <Routes
    routes={[
      {
        path: '/workspaces',
        exact: true,
        component: WorkspacesActions
      }, {
        path: '/workspaces/add',
        exact: true,
        component: WorkspaceActions
      }, {
        path: '/workspaces/:id',
        component: WorkspaceActions
      }
    ]}
  />

const WorkspaceTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/workspaces',
        exact: true,
        component: Workspaces
      }, {
        path: '/workspaces/add',
        exact: true,
        component: Workspace,
        onEnter: () => props.openForm()
      }, {
        path: '/workspaces/:id',
        component: Workspace,
        onEnter: (params) => props.openForm(params.id)
      }
    ]}
  />

WorkspaceTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const WorkspaceTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('workspaces.current', id))
    }
  })
)(WorkspaceTabComponent)

export {
  WorkspaceTab,
  WorkspaceTabActions
}
