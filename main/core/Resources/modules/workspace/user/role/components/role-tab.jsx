import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {matchPath, Routes, withRouter} from '#/main/core/router'

import {PageActions} from '#/main/core/layout/page/components/page-actions.jsx'
import {FormPageActionsContainer} from '#/main/core/data/form/containers/page-actions.jsx'

import {select}  from '#/main/core/workspace/user/selectors'
import {Role}    from '#/main/core/workspace/user/role/components/role.jsx'
import {Roles}   from '#/main/core/workspace/user/role/components/roles.jsx'
import {actions} from '#/main/core/workspace/user/role/actions'

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
        type: 'link',
        icon: 'fa fa-plus',
        label: trans('add_role'),
        target: '/roles/form'
      }}
      cancel={{
        type: 'link',
        target: '/roles',
        exact: true
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
  workspace: T.object.isRequired
}

const RoleTab = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    openForm(id = null, workspace) {
      dispatch(actions.open('roles.current', id, {
        type: 2, //workspace todo : ugly
        workspace
      }))
    }
  })
)(RoleTabComponent)

export {
  RoleTabActions,
  RoleTab
}
