import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'
import {navigate, matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {Workspace}  from '#/main/core/administration/workspace/workspace/components/workspace.jsx'
import {Workspaces} from '#/main/core/administration/workspace/workspace/components/workspaces.jsx'
import {actions}    from '#/main/core/administration/workspace/workspace/actions'

const WorkspaceTabActionsComponent = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="workspaces.current"
      target={(workspace, isNew) => isNew ?
        ['apiv2_workspace_create'] :
        ['apiv2_workspace_update', {id: workspace.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/workspaces/form'})}
      open={{
        icon: 'fa fa-plus',
        label: t('add_workspace'),
        action: '#/workspaces/form'
      }}
      cancel={{
        action: () => navigate('/workspaces')
      }}
    />
  </PageActions>

WorkspaceTabActionsComponent.propTypes = {
  location: T.shape({
    pathname: T.string
  }).isRequired
}

const WorkspaceTabActions = withRouter(WorkspaceTabActionsComponent)

const WorkspaceTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/workspaces',
        exact: true,
        component: Workspaces
      }, {
        path: '/workspaces/form/:id?',
        component: Workspace,
        onEnter: (params) => props.openForm(params.id || null)
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
