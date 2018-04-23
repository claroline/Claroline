import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {actions} from '#/main/core/workspace/user/group/actions'
import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DELETE_CONFIRM} from '#/main/core/layout/modal'
import {select} from '#/main/core/workspace/user/selectors'
import {getGroupList} from '#/main/core/workspace/user/group/components/group-list.jsx'

const GroupsList = props =>
  <DataListContainer
    name="groups.list"
    open={getGroupList(props.workspace).open}
    fetch={{
      url: ['apiv2_workspace_list_groups', {id: props.workspace.uuid}],
      autoload: true
    }}
    actions={(rows) => [
      {
        type: 'callback',
        icon: 'fa fa-fw fa-trash-o',
        label: trans('unregister'),
        callback: () => props.unregister(rows, props.workspace),
        dangerous: true
      }]}
    definition={getGroupList(props.workspace).definition}
    card={getGroupList(props.workspace).card}
  />

GroupsList.propTypes = {
  workspace: T.object,
  unregister: T.func
}

const Groups = connect(
  state => ({workspace: select.workspace(state)}),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: trans('unregister_groups'),
          question: trans('unregister_groups'),
          handleConfirm: () => dispatch(actions.unregister(users, workspace))
        })
      )
    }
  })
)(GroupsList)

export {
  Groups
}
