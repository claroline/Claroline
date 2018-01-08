import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t, Translator} from '#/main/core/translation'
import {generateUrl} from '#/main/core/api/router'

import {PageActions, PageAction} from '#/main/core/layout/page/components/page-actions.jsx'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import Configuration from '#/main/core/library/Configuration/Configuration'
import {MODAL_URL} from '#/main/core/layout/modal'

import {actions} from '#/main/core/administration/user/user/actions'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

const UsersActions = () =>
  <PageActions>
    <PageAction
      id="user-add"
      icon="fa fa-plus"
      title={t('add_user')}
      action="#/users/add"
      primary={true}
    />
  </PageActions>

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
        displayed: (rows) => !rows[0].meta.enabled,
        action: (rows) => props.enable(rows[0])
      }, {
        icon: 'fa fa-fw fa-times-circle-o',
        label: t('disable_user'),
        context: 'row', // todo should be a selection action too
        displayed: (rows) => rows[0].meta.enabled,
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
  deleteWorkspace: T.func.isRequired
}

function mapDispatchToProps(dispatch) {
  return {
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
    }
  }
}

const Users = connect(null, mapDispatchToProps)(UsersList)

export {
  UsersActions,
  Users
}
