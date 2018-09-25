import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans, transChoice} from '#/main/core/translation'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {MODAL_CONFIRM} from '#/main/app/modals/confirm'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {MODAL_WORKSPACE_ROLES} from '#/main/core/administration/workspace/workspace/modals/registration/index'

import {UserList} from '#/main/core/administration/user/user/components/user-list'
import {GroupList} from '#/main/core/administration/user/group/components/group-list'

import {actions as modalActions} from '#/main/app/overlay/modal/store'

import {ListData} from '#/main/app/content/list/containers/data'

import {actions} from '#/main/core/administration/workspace/workspace/actions'
import {WorkspaceList} from '#/main/core/administration/workspace/workspace/components/workspace-list'

// todo : restore custom actions the same way resource actions are implemented
const WorkspacesList = props =>
  <ListData
    name="workspaces.list"
    fetch={{
      url: ['apiv2_administrated_list'],
      autoload: true
    }}
    definition={WorkspaceList.definition}

    primaryAction={WorkspaceList.open}
    actions={(rows) => [
      {
        type: LINK_BUTTON,
        icon: 'fa fa-fw fa-cog',
        label: trans('configure', {}, 'actions'),
        target: `/workspaces/form/${rows[0].uuid}`
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-copy',
        label: trans('duplicate'),
        callback: () => props.copyWorkspaces(rows, false)
      }, {
        type: CALLBACK_BUTTON,
        icon: 'fa fa-fw fa-clone',
        label: trans('duplicate_model'),
        callback: () => props.copyWorkspaces(rows, true)
      }, {
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-user',
        label: trans('register_users'),
        modal: [MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-user',
          title: trans('register'),
          confirmText: trans('register'),
          name: 'selected.user',
          definition: UserList.definition,
          card: UserList.card,
          fetch: {
            url: ['apiv2_user_list_managed_organization'],
            autoload: true
          },
          onEntering:() => props.loadRoles(rows),
          handleSelect: (users) => {
            props.showRolesModal(rows, users, 'user')
          }
        }]
      }, {
        type: MODAL_BUTTON,
        icon: 'fa fa-fw fa-users',
        label: trans('register_groups'),
        modal: [MODAL_DATA_LIST, {
          icon: 'fa fa-fw fa-users',
          title: trans('register'),
          confirmText: trans('register'),
          name: 'selected.group',
          definition: GroupList.definition,
          card: GroupList.card,
          onEntering:() => props.loadRoles(rows),
          fetch: {
            url: ['apiv2_group_list_managed'],
            autoload: true
          },
          handleSelect: (groups) => {
            props.showRolesModal(rows, groups, 'group')
          }
        }]
      },
      // TODO / FIXME : Uses component delete option.
      // Not possible for the moment because it is not possible to display an alert message if the workspace contains not deletable resources.
      {
        type: CALLBACK_BUTTON,
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
  deleteWorkspaces: T.func.isRequired,
  registerUsers: T.func.isRequired,
  registerGroups: T.func.isRequired,
  loadRoles: T.func.isRequired,
  showRolesModal: T.func.isRequired
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
    },

    loadRoles(workspaces) {
      dispatch(actions.loadRoles(workspaces))
    },

    showRolesModal(workspaces, objects, mode) {
      dispatch(modalActions.showModal(MODAL_WORKSPACE_ROLES, {workspaces, objects, mode}))
    }
  })
)(WorkspacesList)

export {
  Workspaces
}
