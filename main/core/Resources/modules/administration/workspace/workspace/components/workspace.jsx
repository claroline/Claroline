import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'
import {url} from '#/main/core/api/router'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {actions as formActions} from '#/main/core/data/form/actions'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {actions} from '#/main/core/administration/workspace/workspace/actions'

import {Workspace as WorkspaceTypes} from '#/main/core/workspace/prop-types'
import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'

const WorkspaceForm = (props) => {
  const roleId = props.workspace.roles !== undefined && props.workspace.roles.length > 0 ?
    props.workspace.roles.find(role => role.name.indexOf('ROLE_WS_MANAGER') > -1).id:
    null

  return (
    <FormContainer
      level={3}
      name="workspaces.current"
      sections={[
        {
          title: trans('general'),
          primary: true,
          fields: [
            {
              name: 'name',
              type: 'string',
              label: trans('name'),
              required: true
            }, {
              name: 'code',
              type: 'string',
              label: trans('code'),
              required: true
            }, {
              name: 'meta.description',
              type: 'html',
              label: trans('description')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-user-plus',
          title: trans('registration'),
          fields: [
            {
              name: 'registration.url',
              type: 'url',
              label: trans('registration_url'),
              calculated: () => url(['claro_workspace_subscription_url_generate', {slug: props.workspace.meta ? props.workspace.meta.slug : ''}, true]),
              required: true,
              disabled: true,
              displayed: !props.new
            }, {
              name: 'registration.selfRegistration',
              type: 'boolean',
              label: trans('activate_self_registration'),
              help: trans('self_registration_workspace_help'),
              linked: [
                {
                  name: 'registration.validation',
                  type: 'boolean',
                  label: trans('validate_registration'),
                  help: trans('validate_registration_help'),
                  displayed: props.workspace.registration && props.workspace.registration.selfRegistration
                }
              ]
            }, {
              name: 'registration.selfUnregistration',
              type: 'boolean',
              label: trans('activate_self_unregistration'),
              help: trans('self_unregistration_workspace_help')
            }
          ]
        }, {
          icon: 'fa fa-fw fa-key',
          title: trans('access_restrictions'),
          fields: [
            {
              name: 'restrictions.hidden',
              type: 'boolean',
              label: trans('hide_in_workspace_list')
            }, {
              name: 'access_max_users',
              type: 'boolean',
              label: trans('access_max_users'),
              calculated: () => props.workspace.restrictions && null !== props.workspace.restrictions.maxUsers && '' !== props.workspace.restrictions.maxUsers,
              onChange: checked => {
                if (checked) {
                  // initialize with the current nb of users with the role
                  props.updateProp('restrictions.maxUsers', 0)
                } else {
                  // reset max users field
                  props.updateProp('restrictions.maxUsers', null)
                }
              },
              linked: [
                {
                  name: 'restrictions.maxUsers',
                  type: 'number',
                  label: trans('maxUsers'),
                  displayed: props.workspace.restrictions && null !== props.workspace.restrictions.maxUsers && '' !== props.workspace.restrictions.maxUsers,
                  required: true,
                  options: {
                    min: 0
                  }
                }
              ]
            }
          ]
        }
      ]}
    >
      <FormSections
        level={3}
      >
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
            deleteAction={() => ({
              type: 'url',
              target: ['apiv2_workspace_remove_organizations', {id: props.workspace.uuid}]
            })}
            definition={OrganizationList.definition}
            card={OrganizationList.card}
          />
        </FormSection>

        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-user"
          title={trans('managers')}
          disabled={props.new}
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
            deleteAction={() => ({
              type: 'url',
              target: ['apiv2_role_remove_users', {id: roleId}]
            })}
            definition={UserList.definition}
            card={UserList.card}
          />
        </FormSection>
      </FormSections>
    </FormContainer>)
}

WorkspaceForm.propTypes = {
  new: T.bool.isRequired,
  workspace: T.shape(
    WorkspaceTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired,
  pickOrganizations: T.func.isRequired,
  pickManagers: T.func.isRequired
}

const Workspace = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'workspaces.current')),
    workspace: formSelect.data(formSelect.form(state, 'workspaces.current'))
  }),
  dispatch =>({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('workspaces.current', propName, propValue))
    },
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
