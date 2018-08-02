import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {Routes} from '#/main/app/router'
import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions'
import {LINK_BUTTON} from '#/main/app/buttons'

import {select}  from '#/main/core/workspace/user/selectors'
import {Role}    from '#/main/core/workspace/user/role/components/role'
import {Roles}   from '#/main/core/workspace/user/role/components/roles'
import {actions} from '#/main/core/workspace/user/role/actions'

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
