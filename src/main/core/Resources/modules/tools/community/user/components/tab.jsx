import React from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'

import {url} from '#/main/app/api'
import {trans} from '#/main/app/intl/translation'
import {Routes} from '#/main/app/router'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {ToolPage} from '#/main/core/tool/containers/page'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_ROLES} from '#/main/core/modals/roles'

import {Users} from '#/main/core/tools/community/user/containers/users'
import {User} from '#/main/core/tools/community/user/components/user'

const UserTab = props =>
  <ToolPage
    path={[{
      type: LINK_BUTTON,
      label: trans('users'),
      target: `${props.path}/users`
    }]}
    subtitle={trans('users')}
    primaryAction="register create"
    actions={[
      {
        name: 'register',
        type: MODAL_BUTTON,
        label: trans('register_users'),
        icon: 'fa fa-plus',
        primary: true,
        displayed: 'workspace' === props.contextType && props.canCreate,

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
              filters: [],
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
        name: 'create',
        type: LINK_BUTTON,
        label: trans('create_user', {}, 'actions'),
        icon: 'fa fa-plus',
        target: `${props.path}/users/form`,
        displayed: 'desktop' === props.contextType && props.canCreate && !props.limitReached,
        primary: true
      }, {
        name: 'export',
        type: URL_BUTTON,
        icon: 'fa fa-fw fa-download',
        label: trans('export', {}, 'actions'),
        target: url(['apiv2_user_csv'])+props.listQueryString,
        group: trans('transfer')
      }
    ]}
  >
    <Routes
      path={props.path}
      routes={[
        {
          path: '/users',
          exact: true,
          component: Users
        }, {
          path: '/users/form/:id?',
          component: User,
          onEnter: (params) => props.open(params.id || null, props.defaultRole)
        }
      ]}
    />
  </ToolPage>

UserTab.propTypes = {
  path: T.string.isRequired,
  contextType: T.string,
  contextData: T.object,
  listQueryString: T.string,
  limitReached: T.bool.isRequired,

  canCreate: T.bool.isRequired,
  defaultRole: T.object, // for user creation
  addUsersToRoles: T.func.isRequired,
  open: T.func.isRequired
}

export {
  UserTab
}
