import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {navigate, matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {Role}    from '#/main/core/workspace/user/role/components/role.jsx'
import {Roles}   from '#/main/core/workspace/user/role/components/role-list.jsx'
import {actions} from '#/main/core/workspace/user/role/actions'
import {select}  from '#/main/core/workspace/user/selectors'

const RoleTabActionsComponent = props =>
  <PageActions>
    <FormPageActionsContainer
      formName="roles.current"
      target={(role, isNew) => isNew ?
        ['apiv2_role_create'] :
        ['apiv2_role_update', {id: role.id}]
      }
      opened={!!matchPath(props.location.pathname, {path: '/roles/form'})}
      open={{
        icon: 'fa fa-plus',
        label: trans('add_role'),
        action: '#/roles/form'
      }}
      cancel={{
        action: () => navigate('/roles')
      }}
    />
  </PageActions>

RoleTabActionsComponent.propTypes = {
  location: T.object
}

const RoleTabActions = withRouter(RoleTabActionsComponent)

const RoleTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/roles',
        exact: true,
        component: Roles
      }, {
        path: '/roles/form/:id?',
        component: Role,
        onEnter: (params) => props.openForm(params.id || null, props.workspace)
      }
    ]}
  />

RoleTabComponent.propTypes = {
  openForm: T.func.isRequired,
  workspace: T.object.isRequired,
  restrictions: T.object.isRequired
}

const RoleTab = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    openForm(id = null, workspace) {

      const defaultValue = {
        type: 2, //workspace
        workspace
      }

      dispatch(actions.open('roles.current', id, defaultValue))
    }
  })
)(RoleTabComponent)

export {
  RoleTabActions,
  RoleTab
}
