import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions as pendingActions} from '#/main/core/workspace/user/pending/actions'
import {MODAL_CONFIRM_REGISTRATION} from '#/main/core/workspace/user/pending/components/modal/confirm-registration.jsx'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

import {select} from '#/main/core/workspace/user/selectors'

const PendingList = props =>
  <DataListContainer
    name="pending.list"
    open={UserList.open}
    fetch={{
      url: ['apiv2_workspace_list_pending', {id: props.workspace.uuid}],
      autoload: true
    }}
    actions={[{
      icon: 'fa fa-fw fa-check',
      label: trans('validate'),
      action: (rows) => props.register(rows, props.workspace)
    }]}
    definition={UserList.definition}
    card={UserList.card}
  />

PendingList.propTypes = {
  workspace: T.object,
  register: T.func
}

const PendingTab = connect(
  state => ({
    workspace: select.workspace(state)
  }),
  dispatch => ({
    register(users, workspace) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM_REGISTRATION, {
          //make a user id list after that
          register: (users, workspace) => dispatch(pendingActions.register(users, workspace)),
          users: users,
          workspace: workspace
        })
      )
    }
  })
)(PendingList)

export {
  PendingTab
}
