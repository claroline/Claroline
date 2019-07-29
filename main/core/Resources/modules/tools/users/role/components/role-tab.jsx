import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {Routes} from '#/main/app/router'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/users/store'
import {actions} from '#/main/core/tools/users/role/store'
import {Role} from '#/main/core/tools/users/role/components/role'
import {Roles} from '#/main/core/tools/users/role/components/roles'

const RoleTabComponent = props =>
  <Routes
    path={props.path}
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
  path: T.string.isRequired,
  workspace: T.object.isRequired,
  openForm: T.func.isRequired
}

const RoleTab = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    openForm(id = null, workspace) {
      dispatch(actions.open(selectors.STORE_NAME + '.roles.current', id, {
        type: 2, //workspace todo : ugly
        workspace
      }))
    }
  })
)(RoleTabComponent)

export {
  RoleTab
}
