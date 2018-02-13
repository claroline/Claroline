import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t, Translator} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import Configuration from '#/main/core/library/Configuration/Configuration'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CHANGE_PASSWORD} from '#/main/core/user/modals/components/change-password.jsx'
import {MODAL_URL} from '#/main/core/layout/modal'
import {actions as userActions} from '#/main/core/user/actions'

import {actions} from '#/main/core/administration/user/user/actions'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

const UsersList = props =>
  <DataListContainer
    name="users.list"
    open={UserList.open}
    fetch={{
      url: ['apiv2_user_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_user_delete_bulk']
    }}
    actions={[
      {
        icon: 'fa fa-fw fa-id-card-o',
        label: t('show_profile'),
        action: (rows) => window.location = generateUrl('claro_user_profile', {publicUrl: rows[0].meta.publicUrl}),
        context: 'row'
      },
      {
        icon: 'fa fa-fw fa-pencil',
        label: t('change_password'),
        context: 'row',
        action: (rows) => props.updatePassword(rows[0]),
        dangerous: true
      }, {
        icon: 'fa fa-fw fa-line-chart',
        label: t('show_tracking'),
        action: (rows) => window.location = generateUrl('claro_user_tracking', {publicUrl: rows[0].meta.publicUrl}),
        context: 'row'
      }, {
        icon: 'fa fa-fw fa-eye',
        label: t('show_as'),
        action: (rows) => window.location = generateUrl('claro_desktop_open', {_switch: rows[0].username}),
        context: 'row'
      }, {
        icon: 'fa fa-fw fa-check-circle-o',
        label: t('enable_user'),
        context: 'row', // todo should be a selection action too
        displayed: (rows) => rows[0].restrictions.disabled,
        action: (rows) => props.enable(rows[0])
      }, {
        icon: 'fa fa-fw fa-times-circle-o',
        label: t('disable_user'),
        context: 'row', // todo should be a selection action too
        displayed: (rows) => !rows[0].restrictions.disabled,
        action: (rows) => props.disable(rows[0]),
        dangerous: true
      }, {
        icon: 'fa fa-fw fa-book',
        label: t('enable_personal_ws'),
        context: 'row', // todo should be a selection action too
        displayed: (rows) => !rows[0].meta.personalWorkspace,
        action: (rows) => props.createWorkspace(rows[0])
      }, {
        icon: 'fa fa-fw fa-book',
        label: t('disable_personal_ws'),
        context: 'row', // todo should be a selection action too
        displayed: (rows) => rows[0].meta.personalWorkspace,
        action: (rows) => props.deleteWorkspace(rows[0]),
        dangerous: true
      },
      ...Configuration.getUsersAdministrationActions().map(action => action.options.modal ? {
        icon: action.icon,
        label: action.name(Translator),
        action: (rows) => props.showModal(MODAL_URL, {
          url: action.url(rows[0].id)
        }),
        context: 'row'
      } : {
        icon: action.icon,
        label: action.name(Translator),
        action: (rows) => window.location = action.url(rows[0].id),
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
          changePassword: (password) => dispatch(userActions.changePassword(user, password))
        })
      )
    }
  })
)(UsersList)

export {
  Users
}
