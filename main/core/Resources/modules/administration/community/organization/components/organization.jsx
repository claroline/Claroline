import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {ListData} from '#/main/app/content/list/containers/data'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions} from '#/main/core/administration/community/organization/store'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {UserList} from '#/main/core/administration/community/user/components/user-list'
import {WorkspaceList} from '#/main/core/workspace/list/components/workspace-list'

const OrganizationForm = props =>
  <FormData
    level={3}
    name={`${baseSelectors.STORE_NAME}.organizations.current`}
    buttons={true}
    target={(organization, isNew) => isNew ?
      ['apiv2_organization_create'] :
      ['apiv2_organization_update', {id: organization.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/organizations',
      exact: true
    }}
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
          }
        ]
      }, {
        title: trans('limit'),
        fields: [
          {
            name: 'limit.enable',
            type: 'boolean',
            label: trans('enable'),
            required: true,
            onChange: (enabled) => {
              props.updateLimit(enabled)
            },
            linked: [
              {
                name: 'limit.users',
                type: 'number',
                label: trans('users'),
                required: true,
                displayed: () =>  props.organization.limit ? props.organization.limit.users > -1: false
              }
            ]
          }
        ]
      }, {
        title: trans('information'),
        fields: [
          {
            name: 'parent',
            type: 'organization',
            label: trans('parent'),
            options: {
              filterChoices: (value, key) => props.organization.id !== key
            }
          }, {
            name: 'email',
            type: 'email',
            label: trans('email')
          }, {
            name: 'vat',
            label: trans('vat_number'),
            type: 'string',
            required: false
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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_workspace'),
            callback: () => props.pickWorkspaces(props.organization.id)
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.organizations.current.workspaces`}
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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            callback: () => props.pickUsers(props.organization.id)
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.organizations.current.users`}
          fetch={{
            url: ['apiv2_organization_list_users', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/users/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            callback: () => props.pickGroups(props.organization.id)
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.organizations.current.groups`}
          fetch={{
            url: ['apiv2_organization_list_groups', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/groups/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
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
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_managers'),
            callback: () => props.pickManagers(props.organization.id)
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.organizations.current.managers`}
          fetch={{
            url: ['apiv2_organization_list_managers', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/users/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_organization_remove_managers', {id: props.organization.id}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

OrganizationForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  organization: T.shape({
    id: T.string,
    limit: T.shape({
      enable: T.boolean,
      users: T.integer
    })
  }).isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired,
  pickWorkspaces: T.func.isRequired,
  pickManagers: T.func.isRequired,
  updateLimit: T.func.isRequired
}

const Organization = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.organizations.current')),
    organization: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.organizations.current'))
  }),
  dispatch => ({
    updateLimit(enabled) {
      dispatch(actions.updateLimit(enabled))
    },
    pickUsers(organizationId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_users'),
        confirmText: trans('add'),
        name: baseSelectors.STORE_NAME+'.users.picker',
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
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_managers'),
        confirmText: trans('add'),
        name: baseSelectors.STORE_NAME+'.users.picker',
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
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-users',
        title: trans('add_groups'),
        confirmText: trans('add'),
        name: baseSelectors.STORE_NAME+'.groups.picker',
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
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-books',
        title: trans('add_workspaces'),
        confirmText: trans('add'),
        name: baseSelectors.STORE_NAME+'.workspaces.picker',
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
