import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/core/administration/users/store'
import {Role}    from '#/main/core/administration/users/role/components/role'
import {Roles}   from '#/main/core/administration/users/role/components/roles'
import {actions} from '#/main/core/administration/users/role/store'

const RoleTabActionsComponent = (props) =>
  <PageActions>
    <PageAction
      type={LINK_BUTTON}
      icon="fa fa-plus"
      label={trans('add_role')}
      target={`${props.path}/roles/form`}
      primary={true}
    />
  </PageActions>

RoleTabActionsComponent.propTypes = {
  path: T.string.isRequired
}

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
        onEnter: (params) => props.openForm(params.id || null)
      }
    ]}
  />

RoleTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired
}

const RoleTabActions = connect(
  (state) => ({
    path: toolSelectors.path(state)
  })
)(RoleTabActionsComponent)

const RoleTab = connect(
  (state) => ({
    path: toolSelectors.path(state)
  }),
  dispatch => ({
    openForm(id = null) {
      dispatch(actions.open(baseSelectors.STORE_NAME+'.roles.current', id))
    }
  })
)(RoleTabComponent)

export {
  RoleTabActions,
  RoleTab
}
