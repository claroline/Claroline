import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {ListData} from '#/main/app/content/list/containers/data'

import {trans, transChoice} from '#/main/app/intl/translation'
import {MODAL_USER_PASSWORD} from '#/main/core/user/modals/password'
import {actions as userActions} from '#/main/core/user/actions'
import {actions} from '#/main/core/administration/user/user/actions'
import {UserList, getUserListDefinition} from '#/main/core/administration/user/user/components/user-list'
import {getActions} from '#/main/core/user/utils'

// todo : restore custom actions the same way resource actions are implemented

const UsersList = props =>
  <ListData
    name="users.list"
    fetch={{
      url: ['apiv2_user_list_managed_organization'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_user_delete_bulk']
    }}
    primaryAction={UserList.open}
    actions={(rows) => getActions(rows, {
      enable: props.enable,
      disable: props.disable,
      createWorkspace: props.createWorkspace,
      deleteWorkspace: props.deleteWorkspace,
      updatePassword: props.updatePassword,
      resetPassword: props.resetPassword
    })}
    definition={getUserListDefinition({platformRoles: props.platformRoles})}
    card={UserList.card}
  />

UsersList.propTypes = {
  enable: T.func.isRequired,
  disable: T.func.isRequired,
  createWorkspace: T.func.isRequired,
  deleteWorkspace: T.func.isRequired,
  updatePassword: T.func.isRequired,
  resetPassword: T.func.isRequired,
  platformRoles: T.array.isRequired
}

UsersList.defaultProps = {
  platformRoles: []
}

const Users = connect(
  state => ({
    platformRoles: state.platformRoles
  }),
  dispatch => ({
    enable(users) {
      dispatch(actions.enable(users))
    },
    disable(users) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-times-circle',
          title: transChoice('disable_users', users.length, {count: users.length}),
          question: trans('disable_users_confirm', {users_list: users.map(u => `${u.firstName} ${u.lastName}`).join(', ')}),
          dangerous: true,
          handleConfirm: () => dispatch(actions.disable(users))
        })
      )
    },
    createWorkspace(users) {
      dispatch(actions.createWorkspace(users))
    },
    deleteWorkspace(users) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-book',
          title: transChoice('disable_personal_workspaces', users.length, {count: users.length}),
          question: trans('disable_personal_workspaces_confirm', {users_list: users.map(u => `${u.firstName} ${u.lastName}`).join(', ')}),
          dangerous: true,
          handleConfirm: () => dispatch(actions.deleteWorkspace(users))
        })
      )
    },
    updatePassword(user) {
      dispatch(
        modalActions.showModal(MODAL_USER_PASSWORD, {
          changePassword: (password) => dispatch(userActions.updatePassword(user, password))
        })
      )
    },
    resetPassword(users) {
      dispatch(actions.resetPassword(users))
    }
  })
)(UsersList)

export {
  Users
}
