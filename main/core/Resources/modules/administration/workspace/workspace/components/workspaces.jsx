import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans, transChoice} from '#/main/core/translation'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {actions as modalActions} from '#/main/app/overlay/modal/store'

import {DataListContainer} from '#/main/core/data/list/containers/data-list'

import {actions} from '#/main/core/administration/workspace/workspace/actions'
import {WorkspaceList} from '#/main/core/administration/workspace/workspace/components/workspace-list.jsx'

// TODO : restore custom actions the same way resource actions

const WorkspacesList = props =>
  <DataListContainer
    name="workspaces.list"
    fetch={{
      url: ['apiv2_workspace_list'],
      autoload: true
    }}
    definition={WorkspaceList.definition}

    primaryAction={WorkspaceList.open}
    actions={(rows) => [
      {
        type: 'link',
        icon: 'fa fa-fw fa-cog',
        label: trans('configure', {}, 'actions'),
        target: `/workspaces/form/${rows[0].uuid}`
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-copy',
        label: trans('duplicate'),
        callback: () => props.copyWorkspaces(rows, false)
      }, {
        type: 'callback',
        icon: 'fa fa-fw fa-clone',
        label: trans('duplicate_model'),
        callback: () => props.copyWorkspaces(rows, true)
      },
      // TODO / FIXME : Uses component delete option.
      // Not possible for the moment because it is not possible to display an alert message if the workspace contains not deletable resources.
      {
        type: 'callback',
        icon: 'fa fa-fw fa-trash-o',
        label: trans('delete'),
        dangerous: true,
        displayed: 0 < rows.filter(w => w.code !== 'default_personal' && w.code !== 'default_workspace').length,
        callback: () => props.deleteWorkspaces(rows)
      }
    ]}

    card={WorkspaceList.card}
  />

WorkspacesList.propTypes = {
  copyWorkspaces: T.func.isRequired,
  deleteWorkspaces: T.func.isRequired
}

const Workspaces = connect(
  null,
  dispatch => ({
    copyWorkspaces(workspaces, asModel = false) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: transChoice(asModel ? 'copy_model_workspaces' : 'copy_workspaces', workspaces.length, {count: workspaces.length}, 'platform'),
          question: trans(asModel ? 'copy_model_workspaces_confirm' : 'copy_workspaces_confirm', {
            workspace_list: workspaces.map(workspace => workspace.name).join(', ')
          }),
          handleConfirm: () => dispatch(actions.copyWorkspaces(workspaces, asModel))
        })
      )
    },

    deleteWorkspaces(workspaces) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: trans('objects_delete_title'),
          question: transChoice('objects_delete_question', workspaces.length, {'count': workspaces.length}, 'platform'),
          dangerous: true,
          handleConfirm: () => dispatch(actions.deleteWorkspaces(workspaces))
        })
      )
    }
  })
)(WorkspacesList)

export {
  Workspaces
}
