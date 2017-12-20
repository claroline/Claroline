import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import Configuration from '#/main/core/library/Configuration/Configuration'
import {t, transChoice, Translator} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_CONFIRM, MODAL_URL, MODAL_USER_PICKER} from '#/main/core/layout/modal'

import {
  PageContainer,
  PageHeader,
  PageContent,
  PageActions,
  PageAction
} from '#/main/core/layout/page'

import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions} from '#/main/core/administration/workspace/actions'
import {WorkspaceList} from '#/main/core/administration/workspace/components/workspace-list.jsx'

const WorkspacesPage = props =>
  <PageContainer id="workspace-management">
    <PageHeader title={t('workspaces_management')}>
      <PageActions>
        <PageAction
          id="workspace-add"
          title={t('create_workspace')}
          icon="fa fa-plus"
          primary={true}
          action={generateUrl('claro_workspace_creation_form')}
        />

        <PageAction
          id="workspaces-import"
          title={t('import_csv')}
          icon="fa fa-download"
          action={generateUrl('claro_admin_workspace_import_form')}
        />
      </PageActions>
    </PageHeader>

    <PageContent>
      <DataListContainer
        name="workspaces"
        open={WorkspaceList.open}
        fetch={{
          url: generateUrl('apiv2_workspace_list')
        }}
        delete={{
          url: generateUrl('apiv2_workspace_delete_bulk'),
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
          }, {
            icon: 'fa fa-fw fa-user',
            label: t('manage_ws_managers'),
            action: (rows) => props.manageWorkspaceManagers(rows[0]),
            context: 'row'
          }
        ]}

        card={WorkspaceList.card}
      />
    </PageContent>
  </PageContainer>

WorkspacesPage.propTypes = {
  copyWorkspaces: T.func.isRequired,
  manageWorkspaceManagers: T.func.isRequired,
  showModal: T.func.isRequired
}

function mapDispatchToProps(dispatch) {
  return {
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

    manageWorkspaceManagers(workspace) {
      dispatch(
        modalActions.showModal(MODAL_USER_PICKER, {
          title: t('manage_ws_managers'),
          selected: workspace.managers,
          handleSelect: (user) => dispatch(actions.addManager(workspace, user)),
          handleRemove: (user) => dispatch(actions.removeManager(workspace, user))
        })
      )
    },

    showModal(type, props) {
      dispatch(modalActions.showModal(type, props))
    }
  }
}

const Workspaces = connect(null, mapDispatchToProps)(WorkspacesPage)

export {
  Workspaces
}
