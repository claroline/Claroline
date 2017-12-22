import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/core/router'

import {actions} from '#/main/core/administration/user/role/actions'
import {Role,  RoleActions}  from '#/main/core/administration/user/role/components/role.jsx'
import {Roles, RolesActions} from '#/main/core/administration/user/role/components/roles.jsx'

const RoleTabActions = () =>
  <Routes
    routes={[
      {
        path: '/roles',
        exact: true,
        component: RolesActions
      }, {
        path: '/roles/add',
        exact: true,
        component: RoleActions
      }, {
        path: '/roles/:id',
        component: RoleActions
      }
    ]}
  />

const RoleTabComponent = props =>
  <Routes
    routes={[
      {
        path: '/roles',
        exact: true,
        component: Roles
      }, {
        path: '/roles/add',
        exact: true,
        onEnter: () => props.openForm(),
        component: Role
      }, {
        path: '/roles/:id',
        onEnter: (params) => props.openForm(params.id),
        component: Role
      }
    ]}
  />

RoleTabComponent.propTypes = {
  openForm: T.func.isRequired
}

const RoleTab = connect(
  null,
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open('roles.current', id))
    }
  })
)(RoleTabComponent)

export {
  RoleTabActions,
  RoleTab
}
