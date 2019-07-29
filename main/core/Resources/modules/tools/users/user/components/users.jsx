import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans, transChoice} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/users/store'
import {actions} from '#/main/core/tools/users/user/store'
import {getUserList} from '#/main/core/tools/users/user/components/user-list'

const UsersList = props =>
  <ListData
    name={selectors.STORE_NAME + '.users.list'}
    fetch={{
      url: ['apiv2_workspace_list_users', {id: props.workspace.uuid}],
      autoload: true
    }}
    primaryAction={getUserList(props.workspace).open}
    actions={(rows) => [{
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-trash-o',
      label: trans('unregister', {}, 'actions'),
      callback: () => props.unregister(rows, props.workspace),
      dangerous: true,
      disabled: rows.find(row => row.roles.filter(r => r.context === 'group' && props.workspace.roles.findIndex(wr => wr.name === r.name) > -1).length > 0)
    }]}
    definition={getUserList(props.workspace).definition}
    card={getUserList(props.workspace).card}
  />

UsersList.propTypes = {
  workspace: T.object,
  unregister: T.func
}

const Users = connect(
  state => ({
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(modalActions.showModal(MODAL_CONFIRM, {
        title: trans('unregister'),
        question: transChoice('unregister_users_confirm_message', users.length, {'count': users.length}),
        dangerous: true,
        handleConfirm: () => dispatch(actions.unregister(users, workspace))
      }))
    }
  })
)(UsersList)

export {
  Users
}
