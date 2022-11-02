import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/community/administration/community/store'
import {Role}    from '#/main/community/administration/community/role/containers/role'
import {Roles}   from '#/main/community/administration/community/role/components/roles'
import {actions} from '#/main/community/administration/community/role/store'

const RoleTabComponent = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('roles'),
      target: `${props.path}/roles`
    }]}
    subtitle={trans('roles')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: LINK_BUTTON,
        icon: 'fa fa-plus',
        label: trans('add_role'),
        target: `${props.path}/roles/form`,
        primary: true,
        exact: true
      }
    ]}
  >
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
  </ToolPage>

RoleTabComponent.propTypes = {
  path: T.string.isRequired,
  openForm: T.func.isRequired
}

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
  RoleTab
}
