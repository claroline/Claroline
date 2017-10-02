import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t, transChoice, ClarolineTranslator} from '#/main/core/translation'
import {generateUrl} from '#/main/core/fos-js-router'
import {localeDate} from '#/main/core/layout/data/types/date/utils'
import {MODAL_CONFIRM, MODAL_DELETE_CONFIRM, MODAL_URL, MODAL_USER_PICKER} from '#/main/core/layout/modal'

import Configuration from '#/main/core/library/Configuration/Configuration'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {actions} from '#/main/core/administration/workspace/actions'

import {
  PageContainer as Page,
  PageHeader,
  PageContent,
  PageActions,
  PageAction
} from '#/main/core/layout/page'

import {DataListContainer as DataList} from '#/main/core/layout/list/containers/data-list.jsx'

const WorkspacesPage = props =>
  <Page id="workspace-management">
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
      <DataList
        name="workspaces"
        definition={[
          {
            name: 'name',
            label: t('name'),
            renderer: (rowData) => {
              // variables is used because React will use it has component display name (eslint requirement)
              const wsLink = <a href={generateUrl('claro_workspace_open', {workspaceId: rowData.id})}>{rowData.name}</a>

              return wsLink
            },
            displayed: true
          }, {
            name: 'code',
            label: t('code'),
            displayed: true
          }, {
            name: 'meta.model',
            label: t('model'),
            type: 'boolean',
            alias: 'model',
            displayed: true
          }, {
            name: 'meta.created',
            label: t('creation_date'),
            type: 'date',
            alias: 'created',
            displayed: true,
            filterable: false
          }, {
            name: 'meta.personal',
            label: t('personal_workspace'),
            type: 'boolean',
            alias: 'personal'
          }, {
            name: 'display.displayable',
            label: t('displayable_in_workspace_list'),
            type: 'boolean',
            alias: 'displayable'
          }, {
            name: 'createdAfter',
            label: t('created_after'),
            type: 'date',
            displayable: false
          }, {
            name: 'createdBefore',
            label: t('created_before'),
            type: 'date',
            displayable: false
          }, {
            name: 'registration.selfRegistration',
            label: t('public_registration'),
            type: 'boolean',
            alias: 'selfRegistration'
          }, {
            name: 'registration.selfUnregistration',
            label: t('public_unregistration'),
            type: 'boolean',
            alias: 'selfUnregistration'
          }, {
            name: 'restrictions.maxStorage',
            label: t('max_storage_size'),
            alias: 'maxStorage'
          }, {
            name: 'restrictions.maxResources',
            label: t('max_amount_resources'),
            type: 'number',
            alias: 'maxResources'
          }, {
            name: 'restrictions.maxUsers',
            label: t('workspace_max_users'),
            type: 'number',
            alias: 'maxUsers'
          }
        ]}

        actions={[
          ...Configuration.getWorkspacesAdministrationActions().map(action => action.options.modal ? {
            icon: action.icon,
            label: action.name(ClarolineTranslator),
            action: (rows) => props.showModal(MODAL_URL, {
              url: action.url(rows[0].id)
            }),
            context: 'row'
          } : {
            icon: action.icon,
            label: action.name(ClarolineTranslator),
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
          }, {
            icon: 'fa fa-fw fa-trash-o',
            label: t('delete'),
            action: (rows) => props.removeWorkspaces(rows),
            isDangerous: true
          }
        ]}

        card={(row) => ({
          onClick: generateUrl('claro_workspace_open', {workspaceId: row.id}),
          poster: null,
          icon: 'fa fa-book',
          title: row.name,
          subtitle: row.code,
          contentText: row.meta.description,
          flags: [
            row.meta.personal                 && ['fa fa-user',         t('personal_workspace')],
            row.meta.model                    && ['fa fa-object-group', t('model')],
            row.display.displayable           && ['fa fa-eye',          t('displayable_in_workspace_list')],
            row.registration.selfRegistration && ['fa fa-globe',        t('public_registration')]
          ].filter(flag => !!flag),
          footer:
            <span>
              created by <b>{row.meta.creator ? row.meta.creator.name : t('unknown')}</b>
            </span>,
          footerLong:
            <span>
              created at <b>{localeDate(row.meta.created)}</b>,
              by <b>{row.meta.creator ? row.meta.creator.name: t('unknown')}</b>
            </span>
        })}
      />
    </PageContent>
  </Page>

WorkspacesPage.propTypes = {
  removeWorkspaces: T.func.isRequired,
  copyWorkspaces: T.func.isRequired,
  manageWorkspaceManagers: T.func.isRequired,
  showModal: T.func.isRequired
}

function mapDispatchToProps(dispatch) {
  return {
    removeWorkspaces(workspaces) {
      dispatch(
        modalActions.showModal(MODAL_DELETE_CONFIRM, {
          title: transChoice('remove_workspaces', workspaces.length, {count: workspaces.length}, 'platform'),
          question: t('remove_workspaces_confirm', {
            workspace_list: workspaces.map(workspace => workspace.name).join(', ')
          }),
          handleConfirm: () => dispatch(actions.removeWorkspaces(workspaces))
        })
      )
    },

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
