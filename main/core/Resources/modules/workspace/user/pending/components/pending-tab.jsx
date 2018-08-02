import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'

import {actions as pendingActions} from '#/main/core/workspace/user/pending/actions'
import {UserList} from '#/main/core/administration/user/user/components/user-list'

import {select} from '#/main/core/workspace/user/selectors'

const PendingList = props =>
  <ListData
    name="pending.list"
    fetch={{
      url: ['apiv2_workspace_list_pending', {id: props.workspace.uuid}],
      autoload: true
    }}
    primaryAction={UserList.open}
    actions={(rows) => [{
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-check',
      label: trans('validate'),
      callback: () => {
        props.register(rows, props.workspace)
      },
      confirm: {
        title: trans('user_registration'),
        message: trans('workspace_user_register_validation_message', {users: rows.map(user => user.username).join(',')})
      }
    }, {
      type: CALLBACK_BUTTON,
      icon: 'fa fa-fw fa-ban',
      label: trans('remove'),
      callback: () => props.remove(rows, props.workspace),
      confirm: {
        title: trans('user_remove'),
        message: trans('workspace_user_remove_validation_message', {users: rows.map(user => user.username).join(',')})
      }
    }]}
    definition={UserList.definition}
    card={UserList.card}
  />

PendingList.propTypes = {
  workspace: T.object,
  register: T.func,
  remove: T.func
}

const PendingTab = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    register(users, workspace) {
      dispatch(pendingActions.register(users, workspace))
    },
    remove(users, workspace) {
      dispatch(pendingActions.remove(users, workspace))
    }
  })
)(PendingList)

export {
  PendingTab
}
