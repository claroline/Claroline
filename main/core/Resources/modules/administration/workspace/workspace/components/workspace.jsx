import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {actions} from '#/main/core/administration/workspace/workspace/actions'

import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

const WorkspaceForm = (props) => {
  const roleId = props.workspace.roles !== undefined ?
    props.workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1).id:
    null

  return (<FormContainer
    level={3}
    name="workspaces.current"
    sections={[
      {
        id: 'general',
        title: t('general'),
        primary: true,
        fields: [
          {
            name: 'name',
            type: 'string',
            label: t('name'),
            required: true
          },
          {
            name: 'code',
            type: 'string',
            label: t('code'),
            required: true
          }
        ]
      },
      {
        id: 'registration',
        title: t('registration'),
        primary: true,
        fields: [
          {
            name: 'registration.validation',
            type: 'boolean',
            label: t('registration_validation')
          },
          {
            name: 'registration.selfRegistration',
            type: 'boolean',
            label: t('public_registration')
          },
          {
            name: 'registration.selfUnregistration',
            type: 'boolean',
            label: t('public_unregistration')
          }
        ]
      },
      {
        id: 'display',
        title: t('display'),
        fields: [
          {
            name: 'display.displayable',
            type: 'boolean',
            label: t('displayable_in_workspace_list')
          }
        ]
      }
    ]}
  >
    <FormSections
      level={3}
    >
      <FormSection
        id="workspace-organizations"
        icon="fa fa-fw fa-building"
        title={t('organizations')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_organizations'),
            action: () => props.pickOrganizations(props.workspace.uuid)
          }
        ]}
      >
        <DataListContainer
          name="workspaces.current.organizations"
          open={OrganizationList.open}
          fetch={{
            url: ['apiv2_workspace_list_organizations', {id: props.workspace.uuid}],
            autoload: props.workspace.uuid && !props.new
          }}
          delete={{
            url: ['apiv2_workspace_remove_organizations', {id: props.workspace.uuid}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </FormSection>
      <FormSection
        id="workspace-managers"
        icon="fa fa-fw fa-user"
        title={t('managers')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_managers'),
            action: () => props.pickManagers(props.workspace)
          }
        ]}
      >
        <DataListContainer
          name="workspaces.current.managers"
          open={UserList.open}
          fetch={{
            url: ['apiv2_workspace_list_managers', {id: props.workspace.uuid}],
            autoload: props.workspace.uuid && !props.new
          }}
          delete={{
            url: ['apiv2_role_remove_users', {id: roleId}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>
    </FormSections>
  </FormContainer>)
}

WorkspaceForm.propTypes = {
  new: T.bool.isRequired,
  workspace: T.shape({
    uuid: T.string,
    roles: T.array
  }).isRequired,
  pickOrganizations: T.func.isRequired,
  pickManagers: T.func.isRequired
}

const Workspace = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'workspaces.current')),
    workspace: formSelect.data(formSelect.form(state, 'workspaces.current'))
  }),
  dispatch =>({
    pickOrganizations(workspaceId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-buildings',
        title: t('add_organizations'),
        confirmText: t('add'),
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
      //this is not a pretty way to find it but it's ok for now
      const managerRole = workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1)

      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: t('add_managers'),
        confirmText: t('add'),
        name: 'managers.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addManagers(workspace.uuid, selected, managerRole.id))
      }))
    }
  })
)(WorkspaceForm)

export {
  Workspace
}
