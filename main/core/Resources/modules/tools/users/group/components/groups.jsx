import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {ListData} from '#/main/app/content/list/containers/data'

import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/users/store'
import {actions} from '#/main/core/tools/users/group/store'
import {getGroupList} from '#/main/core/tools/users/group/components/group-list'

const GroupsList = props =>
  <ListData
    name={selectors.STORE_NAME + '.groups.list'}
    open={getGroupList(props.workspace).open}
    fetch={{
      url: ['apiv2_workspace_list_groups', {id: props.workspace.uuid}],
      autoload: true
    }}
    actions={(rows) => [
      {
        type: CALLBACK_BUTTON,
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
  state => ({
    workspace: toolSelectors.contextData(state)
  }),
  dispatch => ({
    unregister(users, workspace) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          icon: 'fa fa-fw fa-trash-o',
          title: trans('unregister_groups'),
          question: trans('unregister_groups'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.unregister(users, workspace))
        })
      )
    }
  })
)(GroupsList)

export {
  Groups
}
