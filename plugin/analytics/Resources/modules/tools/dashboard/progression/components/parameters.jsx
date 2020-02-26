import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {AlertBlock} from '#/main/app/alert/components/alert-block'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_ROLES} from '#/main/core/modals/roles'

import {selectors} from '#/plugin/analytics/tools/dashboard/progression/store'

const ProgressionParameters = (props) =>
  <div style={{marginTop: 20}}>
    <AlertBlock
      type="info"
      title={trans('requirements_info_1', {}, 'analytics')}
    >
      <div>
        {trans('requirements_info_2', {}, 'analytics')}
      </div>
      <div>
        {trans('requirements_info_3', {}, 'analytics')}
      </div>
    </AlertBlock>

    <FormSections
      level={3}
      defaultOpened="roles-section"
    >
      <FormSection
        id="roles-section"
        key="roles-section"
        icon="fa fa-fw fa-id-badge"
        title={trans('roles')}
        className="embedded-list-section"
        actions={[
          {
            name: 'add-role',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_roles'),
            modal: [MODAL_ROLES, {
              url: ['apiv2_workspace_list_roles', {id: props.workspace.id}],
              title: trans('add_roles'),
              filters: [],
              selectAction: (selectedRoles) => ({
                type: CALLBACK_BUTTON,
                label: trans('create', {}, 'actions'),
                callback: () => props.addRoles(props.workspace, selectedRoles)
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.requirements.roles'}
          fetch={{
            url: ['apiv2_workspace_requirements_list', {workspace: props.workspace.id, type: 'role'}],
            autoload: true
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/progression/parameters/${row.id}`,
            label: trans('open', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_workspace_requirements_delete', {workspace: props.workspace.id}]
          }}
          definition={[
            {
              name: 'role.translationKey',
              type: 'string',
              label: trans('role'),
              displayed: true,
              primary: true,
              calculated: (row) => trans(row.role.translationKey)
            }
          ]}
        />
      </FormSection>

      <FormSection
        id="users-section"
        key="users-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        className="embedded-list-section"
        actions={[
          {
            name: 'add-user',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            modal: [MODAL_USERS, {
              url: ['apiv2_workspace_list_users', {id: props.workspace.id}],
              title: trans('add_users'),
              selectAction: (selectedUsers) => ({
                type: CALLBACK_BUTTON,
                label: trans('create', {}, 'actions'),
                callback: () => props.addUsers(props.workspace, selectedUsers)
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.requirements.users'}
          fetch={{
            url: ['apiv2_workspace_requirements_list', {workspace: props.workspace.id, type: 'user'}],
            autoload: true
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/progression/parameters/${row.id}`,
            label: trans('open', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_workspace_requirements_delete', {workspace: props.workspace.id}]
          }}
          definition={[
            {
              name: 'userName',
              type: 'string',
              label: trans('user'),
              displayed: true,
              primary: true,
              calculated: (row) => `${row.user.lastName} ${row.user.firstName}`
            }
          ]}
        />
      </FormSection>
    </FormSections>
  </div>

ProgressionParameters.propTypes = {
  path: T.string.isRequired,
  workspace: T.shape(WorkspaceTypes.propTypes).isRequired,
  addRoles: T.func.isRequired,
  addUsers: T.func.isRequired
}

export {
  ProgressionParameters
}
