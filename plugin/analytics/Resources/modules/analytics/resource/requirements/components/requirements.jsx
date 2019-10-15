import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_ROLES} from '#/main/core/modals/roles'

import {selectors}   from '#/plugin/analytics/resource/dashboard/store/selectors'

const Requirements = (props) =>
  <div style={{marginTop: 20}}>
    <FormSections
      level={3}
      defaultOpened="roles-section"
    >
      <FormSection
        id="roles-section"
        key="roles-section"
        icon="fa fa-fw fa-id-badge"
        title={trans('roles')}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_roles'),
            modal: [MODAL_ROLES, {
              url: ['apiv2_workspace_list_roles', {id: props.workspace.uuid}],
              title: trans('add_roles'),
              selectAction: (selectedRoles) => ({
                type: CALLBACK_BUTTON,
                label: trans('create', {}, 'actions'),
                callback: () => props.createRequirements(props.resourceId, selectedRoles, 'role')
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.requirements.roles'}
          fetch={{
            url: ['apiv2_workspace_requirements_resource_list', {resourceNode: props.resourceId, type: 'role'}],
            autoload: true
          }}
          delete={{
            url: ['apiv2_workspace_requirements_resource_remove', {resourceNode: props.resourceId}]
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
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            modal: [MODAL_USERS, {
              url: ['apiv2_workspace_list_users', {id: props.workspace.uuid}],
              title: trans('add_users'),
              selectAction: (selectedUsers) => ({
                type: CALLBACK_BUTTON,
                label: trans('create', {}, 'actions'),
                callback: () => props.createRequirements(props.resourceId, selectedUsers, 'user')
              })
            }]
          }
        ]}
      >
        <ListData
          name={selectors.STORE_NAME + '.requirements.users'}
          fetch={{
            url: ['apiv2_workspace_requirements_resource_list', {resourceNode: props.resourceId, type: 'user'}],
            autoload: true
          }}
          delete={{
            url: ['apiv2_workspace_requirements_resource_remove', {resourceNode: props.resourceId}]
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

Requirements.propTypes = {
  path: T.string.isRequired,
  resourceId: T.string.isRequired,
  workspace: T.shape(WorkspaceType.propTypes).isRequired,
  createRequirements: T.func.isRequired
}

export {
  Requirements
}
