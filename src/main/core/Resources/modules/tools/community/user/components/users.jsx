import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/user/routing'
import {constants} from '#/main/core/user/constants'
import {selectors} from '#/main/core/tools/community/user/store'
import {UserList} from '#/main/core/user/components/list'

const Users = props =>
  <UserList
    name={selectors.LIST_NAME}
    url={!isEmpty(props.workspace) ? ['apiv2_workspace_list_users', {id: props.workspace.id}] : ['apiv2_user_list']}
    primaryAction={(row) => ({
      type: LINK_BUTTON,
      target: route(row, props.path)
    })}
    actions={(rows) => !isEmpty(props.workspace) ? [{
      name: 'unregister',
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-trash-o',
      label: trans('unregister', {}, 'actions'),
      callback: () => props.unregister(rows, props.workspace),
      dangerous: true,
      disabled: -1 !== rows.findIndex(row => -1 !== row.roles.findIndex(r => r.context !== 'group' && r.workspace && r.workspace.id === props.workspace.id)),
      confirm: {
        title: trans('unregister'),
        message: transChoice('unregister_users_confirm_message', rows.length, {count: rows.length})
      }
    }] : []}
    customDefinition={[
      {
        name: 'roles',
        alias: 'role',
        type: 'roles',
        label: trans('roles'),
        calculated: (user) => !isEmpty(props.workspace) ?
          user.roles.filter(role => role.workspace && role.workspace.id === props.workspace.id)
          :
          user.roles.filter(role => constants.ROLE_PLATFORM === role.type),
        displayed: true,
        sortable: false,
        options: {
          picker: !isEmpty(props.workspace) ? {
            url: ['apiv2_workspace_list_roles_configurable', {workspace: props.workspace.id}],
            filters: []
          } : undefined
        }
      }
    ]}
  />

Users.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  unregister: T.func.isRequired
}

export {
  Users
}
