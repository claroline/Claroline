import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool'
import {Alert} from '#/main/app/components/alert'
import {PageListSection} from '#/main/app/page'

import {getPlatformRoles, getWorkspaceRoles} from '#/main/community/utils'
import {UserList as BaseUserList} from '#/main/community/user/components/list'
import {selectors} from '#/main/community/tools/community/user/store'
import {MODAL_REGISTER} from '#/main/community/modals/register'
import {MODAL_USER_CREATION} from '#/main/community/user/modals/creation'

const UserList = props =>
  <ToolPage
    title={trans('users')}
  >
    <PageListSection
      poster={props.poster}
      title={trans('users', {}, 'community')}
      addAction={'workspace' === props.contextType ?
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
            workspace: props.contextData,
            onRegister: props.registerUsers,
            mode: 'users'
          }]
        } : {
          name: 'add',
          type: MODAL_BUTTON,
          label: trans('register_users'),
          icon: 'fa fa-fw fa-plus',
          displayed: props.canRegister && !props.limitReached,
          primary: true,
          modal: [MODAL_USER_CREATION, {
            path: props.path,
            contextId: get(props.contextData, 'id'),
            onCreate: props.registerUsers
          }]
        }
      }
    >
      {props.limitReached && props.canRegister &&
        <Alert type="warning">{trans('users_limit_reached')}</Alert>
      }

      <BaseUserList
        className="mb-5"
        flush={true}
        path={props.path}
        name={selectors.LIST_NAME}
        url={'workspace' === props.contextType ?
          ['apiv2_user_list', {contextId: get(props.contextData, 'id')}] :
          ['apiv2_user_list']
        }

        customActions={(rows) => 'workspace' === props.contextType ? [{
          name: 'unregister',
          type: CALLBACK_BUTTON,
          icon: 'fa fa-fw fa-user-minus',
          label: trans('unregister', {}, 'actions'),
          callback: () => props.unregisterUsers(rows, props.contextData),
          dangerous: true,
          displayed: props.canRegister,
          disabled: -1 === rows.findIndex(row => -1 !== row.roles.findIndex(r => r.context !== 'group' && -1 !== r.name.indexOf(get(props.contextData, 'id')))),
          confirm: {
            message: transChoice('unregister_users_confirm_message', rows.length, {count: rows.length}),
            items:  rows.filter(row => -1 !== row.roles.findIndex(r => r.context !== 'group' && -1 !== r.name.indexOf(get(props.contextData, 'id')))).map(item => ({
              thumbnail: item.picture,
              id: item.id,
              name: item.name
            })),
            additional: trans('unregister_users_confirm_additional')
          }
        }] : []}
        customDefinition={[
          {
            name: 'groups',
            label: trans('groups', {}, 'community'),
            type: 'group',
            options: {
              multiple: true,
              picker: 'workspace' === props.contextType ? {
                url: ['apiv2_workspace_list_groups', {id: get(props.contextData, 'id')}]
              } : undefined
            },
            displayed: false,
            displayable: false,
            sortable: false
          }, {
            name: 'roles',
            type: 'role',
            label: trans('roles'),
            calculated: (user) => 'workspace' === props.contextType ?
              getWorkspaceRoles(user.roles, get(props.contextData, 'id')) :
              getPlatformRoles(user.roles),
            displayed: true,
            filterable: true,
            sortable: false,
            options: {
              multiple: true,
              picker: 'workspace' === props.contextType ? {
                url: ['apiv2_workspace_list_roles', {id: get(props.contextData, 'id')}],
                filters: []
              } : undefined
            }
          }, {
            name: 'teams',
            type: 'team',
            label: trans('teams', {}, 'community'),
            displayable: false,
            displayed: false,
            filterable: 'workspace' === props.contextType,
            sortable: false,
            options: {
              multiple: true,
              picker: {
                url: ['apiv2_team_workspace_list', {id: get(props.contextData, 'id')}]
              }
            }
          }
        ]}
      />
    </PageListSection>
  </ToolPage>

UserList.propTypes = {
  path: T.string.isRequired,
  poster: T.string,
  contextType: T.string.isRequired,
  contextData: T.object,
  canRegister: T.bool.isRequired,
  limitReached: T.bool.isRequired,
  unregisterUsers: T.func.isRequired,
  registerUsers: T.func.isRequired
}

export {
  UserList
}
