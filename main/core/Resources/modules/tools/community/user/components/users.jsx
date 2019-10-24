import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {route} from '#/main/core/user/routing'
import {UserCard} from '#/main/core/user/components/card'
import {constants} from '#/main/core/user/constants'
import {actions, selectors} from '#/main/core/tools/community/user/store'

// TODO : reuse main/core/user/components/list

const UsersList = props =>
  <ListData
    name={selectors.LIST_NAME}
    fetch={{
      url: !isEmpty(props.workspace) ? ['apiv2_workspace_list_users', {id: props.workspace.uuid}] : ['apiv2_visible_users_list'],
      autoload: true
    }}
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
      disabled: rows.find(row => row.roles.filter(r => r.name !== 'ROLE_USER' && r.context === 'group' && props.workspace.roles.findIndex(wr => wr.name === r.name) > -1).length > 0),
      confirm: {
        title: trans('unregister'),
        message: transChoice('unregister_users_confirm_message', rows.length, {count: rows.length})
      }
    }] : []}
    definition={[
      {
        name: 'username',
        type: 'username',
        label: trans('username'),
        displayed: true,
        primary: true
      }, {
        name: 'lastName',
        type: 'string',
        label: trans('last_name'),
        displayed: true
      }, {
        name: 'firstName',
        type: 'string',
        label: trans('first_name'),
        displayed: true
      }, {
        name: 'email',
        alias: 'mail',
        type: 'email',
        label: trans('email'),
        displayed: true
      }, {
        name: 'administrativeCode',
        type: 'string',
        label: trans('code')
      }, {
        name: 'meta.lastLogin',
        type: 'date',
        alias: 'lastLogin',
        label: trans('last_login'),
        displayed: true,
        options: {
          time: true
        }
      }, {
        name: 'roles',
        alias: 'role',
        type: 'roles',
        label: trans('roles'),
        calculated: (user) => !isEmpty(props.workspace) ?
          user.roles.filter(role => role.workspace && role.workspace.id === props.workspace.uuid)
          :
          user.roles.filter(role => constants.ROLE_PLATFORM === role.type),
        displayed: true,
        filterable: true
      }
    ]}
    card={UserCard}
  />

UsersList.propTypes = {
  path: T.string.isRequired,
  workspace: T.object,
  unregister: T.func.isRequired
}

const Users = connect(
  state => ({
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(actions.unregister(users, workspace))
    }
  })
)(UsersList)

export {
  Users
}
