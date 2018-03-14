import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {getUserList} from '#/main/core/workspace/user/user/components/user-list.jsx'
import {actions} from '#/main/core/workspace/user/user/actions'

import {select} from '#/main/core/workspace/user/selectors'

const UsersList = props =>
  <DataListContainer
    name="users.list"
    open={getUserList(props.workspace).open}
    fetch={{
      url: ['apiv2_workspace_list_users', {id: props.workspace.uuid}],
      autoload: true
    }}
    actions={[{
      icon: 'fa fa-fw fa-trash-o',
      label: trans('unregister'),
      action: (rows) => props.unregister(rows, props.workspace),
      dangerous: true
    }]}
    definition={getUserList(props.workspace).definition}
    card={getUserList(props.workspace).card}
  />

UsersList.propTypes = {
  workspace: T.object,
  unregister: T.func
}

const Users = connect(
  state => ({workspace: select.workspace(state)}),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: trans('unregister_users'),
          question: trans('unregister_users'),
          handleConfirm: () => dispatch(actions.unregister(users, workspace))
        })
      )
    }
  })
)(UsersList)

export {
  Users
}
