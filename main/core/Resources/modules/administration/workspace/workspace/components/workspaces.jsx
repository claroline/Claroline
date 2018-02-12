import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import Configuration from '#/main/core/library/Configuration/Configuration'
import {t, transChoice, Translator} from '#/main/core/translation'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CONFIRM, MODAL_URL} from '#/main/core/layout/modal'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions} from '#/main/core/administration/workspace/workspace/actions'
import {WorkspaceList} from '#/main/core/administration/workspace/workspace/components/workspace-list.jsx'

const WorkspacesList = props =>
  <DataListContainer
    name="workspaces.list"
    open={WorkspaceList.open}
    fetch={{
      url: ['apiv2_workspace_list'],
      autoload: true
    }}
    delete={{
      url: ['apiv2_workspace_delete_bulk'],
      displayed: (workspaces) =>
        0 < workspaces.filter(workspace => workspace.code !== 'default_personal' && workspace.code !== 'default_workspace' ).length
    }}
    definition={WorkspaceList.definition}

    actions={[
      ...Configuration.getWorkspacesAdministrationActions().map(action => action.options.modal ? {
        icon: action.icon,
        label: action.name(Translator),
        action: (rows) => props.showModal(MODAL_URL, {
          url: action.url(rows[0].id)
        }),
        context: 'row'
      } : {
        icon: action.icon,
        label: action.name(Translator),
        action: (rows) => action.url(rows[0].id),
        context: 'row'
      }), {
        icon: 'fa fa-fw fa-copy',
        label: t('duplicate'),
        action: (rows) => props.copyWorkspaces(rows, false)
      }, {
        icon: 'fa fa-fw fa-clone',
        label: t('duplicate_model'),
        action: (rows) => props.copyWorkspaces(rows, true)
      },
      {
        icon: 'fa fa-fw fa-book',
        label: t('edit'),
        action: (rows) => window.location.href = `#/workspaces/form/${rows[0].uuid}`
      }
    ]}

    card={WorkspaceList.card}
  />

WorkspacesList.propTypes = {
  copyWorkspaces: T.func.isRequired,
  showModal: T.func.isRequired
}

const Workspaces = connect(
  null,
  dispatch => ({
    copyWorkspaces(workspaces, asModel = false) {
      dispatch(
        modalActions.showModal(MODAL_CONFIRM, {
          title: transChoice(asModel ? 'copy_model_workspaces' : 'copy_workspaces', workspaces.length, {count: workspaces.length}, 'platform'),
          question: t(asModel ? 'copy_model_workspaces_confirm' : 'copy_workspaces_confirm', {
            workspace_list: workspaces.map(workspace => workspace.name).join(', ')
          }),
          handleConfirm: () => dispatch(actions.copyWorkspaces(workspaces, asModel))
        })
      )
    },

    showModal(type, props) {
      dispatch(modalActions.showModal(type, props))
    }
  })
)(WorkspacesList)

export {
  Workspaces
}
