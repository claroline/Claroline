import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {LINK_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {Role} from '#/main/core/tools/community/role/components/role'
import {Roles} from '#/main/core/tools/community/role/components/roles'

const RoleTab = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('roles'),
      target: `${props.path}/roles`
    }]}
    subtitle={trans('roles')}
    actions={[
      {
        name: 'add_role',
        type: LINK_BUTTON,
        label: trans('add_role'),
        icon: 'fa fa-plus',
        target: `${props.path}/roles/form`,
        primary: true,
        displayed: props.canCreate
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
          onEnter: (params) => props.open(params.id || null, props.contextData)
        }
      ]}
    />
  </ToolPage>

RoleTab.propTypes = {
  path: T.string.isRequired,
  contextData: T.object,
  canCreate: T.bool.isRequired,
  open: T.func.isRequired
}

export {
  RoleTab
}
