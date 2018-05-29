import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import isEmpty from 'lodash/isEmpty'

import {trans} from '#/main/core/translation'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {actions} from '#/main/core/administration/workspace/workspace/actions'

import {WorkspaceForm} from '#/main/core/workspace/components/form'
import {WorkspaceMetrics} from '#/main/core/workspace/components/metrics'
import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list'
import {UserList} from '#/main/core/administration/user/user/components/user-list'

const WorkspaceComponent = (props) =>
  <div>
    <WorkspaceMetrics
      workspace={props.workspace}
    />

    <WorkspaceForm name="workspaces.current">
      <FormSections level={3}>
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-building"
          title={trans('organizations')}
          disabled={props.new}
          actions={[
            {
              type: 'callback',
              icon: 'fa fa-fw fa-plus',
              label: trans('add_organizations'),
              callback: () => props.pickOrganizations(props.workspace.uuid)
            }
          ]}
        >
          <DataListContainer
            name="workspaces.current.organizations"
            fetch={{
              url: ['apiv2_workspace_list_organizations', {id: props.workspace.uuid}],
              autoload: props.workspace.uuid && !props.new
            }}
            primaryAction={OrganizationList.open}
            delete={{
              url: ['apiv2_workspace_remove_organizations', {id: props.workspace.uuid}]
            }}
            definition={OrganizationList.definition}
            card={OrganizationList.card}
          />
        </FormSection>

        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('managers')}
          disabled={props.new || isEmpty(props.managerRole)}
          actions={[
            {
              type: 'callback',
              icon: 'fa fa-fw fa-plus',
              label: trans('add_managers'),
              callback: () => props.pickManagers(props.workspace)
            }
          ]}
        >
          <DataListContainer
            name="workspaces.current.managers"
            fetch={{
              url: ['apiv2_workspace_list_managers', {id: props.workspace.uuid}],
              autoload: props.workspace.uuid && !props.new
            }}
            primaryAction={UserList.open}
            delete={{
              url: ['apiv2_role_remove_users', {id: props.managerRole.id}]
            }}
            definition={UserList.definition}
            card={UserList.card}
          />
        </FormSection>
      </FormSections>
    </WorkspaceForm>
  </div>

WorkspaceComponent.propTypes = {
  new: T.bool.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  managerRole: T.shape({
    id: T.string
  }),
  pickOrganizations: T.func.isRequired,
  pickManagers: T.func.isRequired
}

WorkspaceComponent.defaultProps = {
  managerRole: {}
}

const Workspace = connect(
  state => {
    const workspace = formSelect.data(formSelect.form(state, 'workspaces.current'))

    return {
      new: formSelect.isNew(formSelect.form(state, 'workspaces.current')),
      workspace: workspace,
      managerRole: !isEmpty(workspace.roles) ?
        workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1) :
        null
    }
  },
  dispatch =>({
    pickOrganizations(workspaceId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-buildings',
        title: trans('add_organizations'),
        confirmText: trans('add'),
        name: 'organizations.picker',
        definition: OrganizationList.definition,
        card: OrganizationList.card,
        fetch: {
          url: ['apiv2_organization_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addOrganizations(workspaceId, selected))
      }))
    },
    pickManagers(workspace) {
      // this is not a pretty way to find it but it's ok for now
      const managerRole = workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1)

      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_managers'),
        confirmText: trans('add'),
        name: 'managers.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list_managed_workspace'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addManagers(workspace.uuid, selected, managerRole.id))
      }))
    }
  })
)(WorkspaceComponent)

export {
  Workspace
}
