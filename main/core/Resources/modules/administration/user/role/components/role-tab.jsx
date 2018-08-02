import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'

import {Role}    from '#/main/core/administration/user/role/components/role'
import {Roles}   from '#/main/core/administration/user/role/components/roles'
import {actions} from '#/main/core/administration/user/role/actions'

const RoleTabActions = () =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_role')}
      target="/roles/form"
      primary={true}
    />
  </PageActions>

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
        onEnter: (params) => props.openForm(params.id || null)
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
