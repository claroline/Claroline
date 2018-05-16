import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t, Translator} from '#/main/core/translation'
import Configuration from '#/main/core/library/Configuration/Configuration'

import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CHANGE_PASSWORD} from '#/main/core/user/modals/components/change-password.jsx'
import {MODAL_URL} from '#/main/core/layout/modal'
import {actions as userActions} from '#/main/core/user/actions'

import {actions} from '#/main/core/administration/user/user/actions'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

const UsersList = props =>
  <DataListContainer
    name="users.list"
    fetch={{
      url: ['apiv2_user_list_managed_organization'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_user_delete_bulk']
    }}

    primaryAction={UserList.open}
    actions={(rows) => [
      {
        type: 'url',
        icon: 'fa fa-fw fa-id-card-o',
        label: t('show_profile'),
        target: ['claro_user_profile', {publicUrl: rows[0].meta.publicUrl}],
        context: 'row'
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-lock',
        label: t('change_password'),
        context: 'row',
        callback: () => props.updatePassword(rows[0]),
        dangerous: true
      }, {
        type: 'url',
        icon: 'fa fa-fw fa-line-chart',
        label: t('show_tracking'),
        target: ['claro_user_tracking', {publicUrl: rows[0].meta.publicUrl}],
        context: 'row'
      }, {
        type: 'url',
        icon: 'fa fa-fw fa-user-secret',
        label: t('show_as'),
        target: ['claro_desktop_open', {_switch: rows[0].username}],
        context: 'row'
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-check-circle-o',
        label: t('enable_user'),
        context: 'row', // todo should be a selection action too
        displayed: rows[0].restrictions.disabled,
        callback: () => props.enable(rows[0])
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-times-circle-o',
        label: t('disable_user'),
        context: 'row', // todo should be a selection action too
        displayed: !rows[0].restrictions.disabled,
        callback: () => props.disable(rows[0]),
        dangerous: true
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-book',
        label: t('enable_personal_ws'),
        context: 'row', // todo should be a selection action too
        displayed: !rows[0].meta.personalWorkspace,
        callback: () => props.createWorkspace(rows[0])
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-book',
        label: t('disable_personal_ws'),
        context: 'row', // todo should be a selection action too
        displayed: rows[0].meta.personalWorkspace,
        callback: () => props.deleteWorkspace(rows[0]),
        dangerous: true
      },
      ...Configuration.getUsersAdministrationActions().map(action => action.options.modal ? {
        type: 'modal',
        icon: action.icon,
        label: action.name(Translator),
        modal: [MODAL_URL, {
          url: action.url(rows[0].id)
        }],
        context: 'row'
      } : {
        type: 'url',
        icon: action.icon,
        label: action.name(Translator),
        target: action.url(rows[0].id),
        context: 'row'
      })
    ]}
    definition={UserList.definition}
    card={UserList.card}
  />

UsersList.propTypes = {
  enable: T.func.isRequired,
  disable: T.func.isRequired,
  createWorkspace: T.func.isRequired,
  deleteWorkspace: T.func.isRequired,
  updatePassword: T.func.isRequired
}

const Users = connect(
  null,
  dispatch => ({
    enable(user) {
      dispatch(actions.enable(user))
    },
    disable(user) {
      dispatch(actions.disable(user))
    },
    createWorkspace(user) {
      dispatch(actions.createWorkspace(user))
    },
    deleteWorkspace(user) {
      dispatch(actions.deleteWorkspace(user))
    },
    updatePassword(user) {
      dispatch(
        modalActions.showModal(MODAL_CHANGE_PASSWORD, {
          changePassword: (password) => dispatch(userActions.updatePassword(user, password))
        })
      )
    }
  })
)(UsersList)

export {
  Users
}
