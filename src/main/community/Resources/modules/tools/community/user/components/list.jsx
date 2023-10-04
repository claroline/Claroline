import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Alert} from '#/main/app/alert/components/alert'

import {constants} from '#/main/community/constants'
import {getPlatformRoles, getWorkspaceRoles} from '#/main/community/utils'
import {UserList as BaseUserList} from '#/main/community/user/components/list'
import {MODAL_USERS} from '#/main/community/modals/users'
import {MODAL_ROLES} from '#/main/community/modals/roles'

import {MODAL_USER_DISABLE_INACTIVE} from '#/main/community/tools/community/user/modals/disable-inactive'
import {selectors} from '#/main/community/tools/community/user/store'

const UserList = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('users'),
      target: `${props.path}/users`
    }]}
    subtitle={trans('users')}
    primaryAction="add"
    actions={[
      {
        name: 'add',
        type: MODAL_BUTTON,
        label: trans('register_users'),
        icon: 'fa fa-fw fa-plus',
        primary: true,
        displayed: 'workspace' === props.contextType && props.canRegister,

        // select users to register
        modal: [MODAL_USERS, {
          title: trans('register_users'),
          subtitle: trans('workspace_register_select_users'),
          selectAction: (selectedUsers) => ({
            type: MODAL_BUTTON,
            label: trans('select', {}, 'actions'),

            // select roles to assign to selected users
            modal: [MODAL_ROLES, {
              url: ['apiv2_workspace_list_roles', {id: get(props.contextData, 'id')}],
              filters: [
                // those filters are not exploited as the url already do it for us. This is just to disable filters
                {property: 'type', value: constants.ROLE_WORKSPACE, locked: true, hidden: false},
                {property: 'workspace', value: get(props.contextData, 'id'), locked: true, hidden: false}
              ],
              title: trans('register_users'),
              subtitle: trans('workspace_register_select_roles'),
              selectAction: (selectedRoles) => ({
                type: CALLBACK_BUTTON,
                label: trans('register', {}, 'actions'),
                callback: () => props.addUsersToRoles(selectedRoles, selectedUsers)
              })
            }]
          })
        }]
      }, {
        name: 'add',
        type: LINK_BUTTON,
        label: trans('create_user', {}, 'actions'),
        icon: 'fa fa-fw fa-plus',
        target: `${props.path}/users/new`,
        displayed: 'desktop' === props.contextType && props.canRegister && !props.limitReached,
        primary: true
      }, {
        name: 'disable-inactive',
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-user-clock',
        label: trans('disable_inactive_users', {}, 'community'),
        modal: [MODAL_USER_DISABLE_INACTIVE],
        displayed: 'desktop' === props.contextType && props.canAdministrate,
        dangerous: true
      }
    ]}
  >
    {props.limitReached && props.canRegister &&
      <Alert type="warning" style={{marginTop: 20}}>{trans('users_limit_reached')}</Alert>
    }

    <BaseUserList
      path={props.path}
      name={selectors.LIST_NAME}
      url={!isEmpty(props.contextData) ?
        ['apiv2_workspace_list_users', {id: props.contextData.id}] :
        ['apiv2_user_list']
      }
      customActions={(rows) => !isEmpty(props.contextData) ? [{
        name: 'unregister',
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-user-minus',
        label: trans('unregister', {}, 'actions'),
        callback: () => props.unregister(rows, props.contextData),
        dangerous: true,
        displayed: props.canRegister,
        disabled: -1 === rows.findIndex(row => -1 !== row.roles.findIndex(r => r.context !== 'group' && r.workspace && r.workspace.id === props.contextData.id)),
        confirm: {
          title: trans('unregister', {}, 'actions'),
          message: transChoice('unregister_users_confirm_message', rows.length, {count: rows.length})
        }
      }] : []}
      customDefinition={[
        {
          name: 'groups',
          label: trans('groups'),
          type: 'groups',
          options: {
            picker: !isEmpty(props.contextData) ? {
              url: ['apiv2_workspace_list_groups', {id: props.contextData.id}]
            } : undefined
          },
          displayed: false,
          displayable: false,
          sortable: false
        }, {
          name: 'roles',
          type: 'roles',
          label: trans('roles'),
          calculated: (user) => !isEmpty(props.contextData) ?
            getWorkspaceRoles(user.roles, props.contextData.id) :
            getPlatformRoles(user.roles),
          displayed: true,
          filterable: true,
          sortable: false,
          options: {
            picker: !isEmpty(props.contextData) ? {
              url: ['apiv2_workspace_list_roles_configurable', {workspace: props.contextData.id}],
              filters: []
            } : undefined
          }
        }
      ]}
    />
  </ToolPage>

UserList.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object,
  canRegister: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  limitReached: T.bool.isRequired,
  unregister: T.func.isRequired,
  addUsersToRoles: T.func.isRequired
}

export {
  UserList
}
