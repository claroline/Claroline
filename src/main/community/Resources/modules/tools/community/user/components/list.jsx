import React from 'react'
import {PropTypes as T} from 'prop-types'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'
import {Alert} from '#/main/app/components/alert'

import {getPlatformRoles, getWorkspaceRoles} from '#/main/community/utils'
import {UserList as BaseUserList} from '#/main/community/user/components/list'

import {MODAL_USER_DISABLE_INACTIVE} from '#/main/community/tools/community/user/modals/disable-inactive'
import {selectors} from '#/main/community/tools/community/user/store'
import {ContentSizing} from '#/main/app/content/components/sizing'

import {MODAL_REGISTER} from '#/main/community/modals/register'

const UserList = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('users'),
      target: `${props.path}/users`
    }]}
    subtitle={trans('users')}
    /*primaryAction="add"*/
    primaryAction={'workspace' === props.contextType ?
      {
        name: 'add',
        type: MODAL_BUTTON,
        label: trans('register_users'),
        icon: 'fa fa-fw fa-plus',
        primary: true,
        displayed: props.canRegister,

        // select users to register
        modal: [MODAL_REGISTER, {
          title: trans('register_users'),
          subtitle: trans('workspace_register_select_users'),
          workspaces: [props.contextData],
          onRegister: props.registerUsers,
          mode: 'users'
        }]
      } : {
        name: 'add',
        type: LINK_BUTTON,
        label: trans('create_user', {}, 'actions'),
        icon: 'fa fa-fw fa-plus',
        target: `${props.path}/users/new`,
        displayed: props.canRegister && !props.limitReached,
        primary: true
      }
    }
    actions={[
      {
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
      <Alert type="warning" className="mt-3">{trans('users_limit_reached')}</Alert>
    }

    <ContentSizing size="full">
      <BaseUserList
        flush={true}
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
          callback: () => props.unregisterUsers(rows, props.contextData),
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
    </ContentSizing>
  </ToolPage>

UserList.propTypes = {
  path: T.string.isRequired,
  contextType: T.string.isRequired,
  contextData: T.object,
  canRegister: T.bool.isRequired,
  canAdministrate: T.bool.isRequired,
  limitReached: T.bool.isRequired,
  unregisterUsers: T.func.isRequired,
  registerUsers: T.func.isRequired
}

export {
  UserList
}
