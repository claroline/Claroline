import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'

import {actions} from '#/main/core/administration/user/organization/actions'
import {GroupList} from '#/main/core/administration/user/group/components/group-list.jsx'
import {UserList} from '#/main/core/administration/user/user/components/user-list.jsx'
import {WorkspaceList} from '#/main/core/administration/workspace/workspace/components/workspace-list.jsx'

const OrganizationForm = props =>
  <FormContainer
    level={3}
    name="organizations.current"
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
            name: 'type',
            type: 'choice',
            label: trans('type'),
            required: true,
            options: {
              condensed: true,
              choices: {
                'external': trans('external'),
                'internal': trans('internal')
              }
            }
          }, {
            name: 'vat',
            label: trans('vat_number'),
            type: 'string',
            required: false
          }, {
            name: 'parent',
            type: 'organization',
            label: trans('parent'),
            options: {
              filterChoices: (value, key) => props.organization.id !== key
            }
          },  {
            name: 'email',
            type: 'email',
            label: trans('email')
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
        icon="fa fa-fw fa-book"
        title={trans('workspaces')}
        disabled={props.new}
        actions={[
          {
            type: 'callback',
            icon: 'fa fa-fw fa-plus',
            label: trans('add_workspace'),
            callback: () => props.pickWorkspaces(props.organization.id)
          }
        ]}
      >
        <DataListContainer
          name="organizations.current.workspaces"
          fetch={{
            url: ['apiv2_organization_list_workspaces', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={WorkspaceList.open}
          delete={{
            url: ['apiv2_organization_remove_workspaces', {id: props.organization.id}]
          }}
          definition={WorkspaceList.definition}
          card={WorkspaceList.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        disabled={props.new}
        actions={[
          {
            type: 'callback',
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            callback: () => props.pickUsers(props.organization.id)
          }
        ]}
      >
        <DataListContainer
          name="organizations.current.users"
          fetch={{
            url: ['apiv2_organization_list_users', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={UserList.open}
          delete={{
            url: ['apiv2_organization_remove_users', {id: props.organization.id}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>

      <FormSection
        icon="fa fa-fw fa-users"
        className="embedded-list-section"
        title={trans('groups')}
        disabled={props.new}
        actions={[
          {
            type: 'callback',
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            callback: () => props.pickGroups(props.organization.id)
          }
        ]}
      >
        <DataListContainer
          name="organizations.current.groups"
          fetch={{
            url: ['apiv2_organization_list_groups', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={GroupList.open}
          delete={{
            url: ['apiv2_organization_remove_groups', {id: props.organization.id}]
          }}
          definition={GroupList.definition}
          card={GroupList.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-users"
        title={trans('managers')}
        disabled={props.new}
        actions={[
          {
            type: 'callback',
            icon: 'fa fa-fw fa-plus',
            label: trans('add_managers'),
            callback: () => props.pickManagers(props.organization.id)
          }
        ]}
      >
        <DataListContainer
          name="organizations.current.managers"
          fetch={{
            url: ['apiv2_organization_list_managers', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={UserList.open}
          delete={{
            url: ['apiv2_organization_remove_managers', {id: props.organization.id}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>
    </FormSections>
  </FormContainer>

OrganizationForm.propTypes = {
  new: T.bool.isRequired,
  organization: T.shape({
    id: T.string
  }).isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired,
  pickWorkspaces: T.func.isRequired,
  pickManagers: T.func.isRequired
}

const Organization = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'organizations.current')),
    organization: formSelect.data(formSelect.form(state, 'organizations.current'))
  }),
  dispatch => ({
    pickUsers(organizationId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_users'),
        confirmText: trans('add'),
        name: 'users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list_managed_organization'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addUsers(organizationId, selected))
      }))
    },
    pickManagers(organizationId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_managers'),
        confirmText: trans('add'),
        name: 'users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list_managed_organization'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addManagers(organizationId, selected))
      }))
    },
    pickGroups(organizationId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-users',
        title: trans('add_groups'),
        confirmText: trans('add'),
        name: 'groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list_managed'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addGroups(organizationId, selected))
      }))
    },
    pickWorkspaces(organizationId) {
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-books',
        title: trans('add_workspaces'),
        confirmText: trans('add'),
        name: 'workspaces.picker',
        definition: WorkspaceList.definition,
        card: WorkspaceList.card,
        fetch: {
          url: ['apiv2_workspace_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addWorkspaces(organizationId, selected))
      }))
    }
  })
)(OrganizationForm)

export {
  Organization
}
