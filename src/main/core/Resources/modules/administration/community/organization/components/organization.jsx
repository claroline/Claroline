import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import get from 'lodash/get'

import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {actions as formActions, selectors as formSelectors} from '#/main/app/content/form/store'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'
import {MODAL_WORKSPACES} from '#/main/core/modals/workspaces'

import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {actions, selectors} from '#/main/core/administration/community/organization/store'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {UserList} from '#/main/core/administration/community/user/components/user-list'
import workspacesSource from '#/main/core/data/sources/workspaces'

const OrganizationForm = props =>
  <FormData
    level={3}
    name={selectors.FORM_NAME}
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
                external: trans('external'),
                internal: trans('internal')
              }
            }
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'parent',
            type: 'organization',
            label: trans('parent')
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
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.public',
            type: 'boolean',
            label: trans('make_organization_public', {}, 'user'),
            help: [
              trans('make_organization_public_help1', {}, 'user'),
              trans('make_organization_public_help2', {}, 'user')
            ]
          }, {
            name: 'restrictions.maxUsers',
            type: 'boolean',
            label: trans('restrict_users_count'),
            calculated: (organization) => get(organization, 'restrictions.maxUsers') || get(organization, 'restrictions.users', -1) > -1,
            onChange: (enabled) => {
              if (!enabled) {
                props.updateProp('restrictions.users', -1)
              } else {
                props.updateProp('restrictions.users', null)
              }
            },
            linked: [
              {
                name: 'restrictions.users',
                type: 'number',
                label: trans('users_count'),
                displayed: (organization) => get(organization, 'restrictions.maxUsers') || get(organization, 'restrictions.users', -1) > -1
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
        icon="fa fa-fw fa-user-cog"
        title={trans('managers')}
        disabled={props.new}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_managers'),
            modal: [MODAL_USERS, {
              url: ['apiv2_user_list_managed'],
              selectAction: (users) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addManagers(props.organization.id, users.map(user => user.id))
              })
            }]
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

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-book"
        title={trans('workspaces')}
        disabled={props.new}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_workspace'),
            modal: [MODAL_WORKSPACES, {
              url: ['apiv2_workspace_list'],
              selectAction: (workspaces) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addWorkspaces(props.organization.id, workspaces.map(workspace => workspace.id))
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.organizations.current.workspaces`}
          fetch={{
            url: ['apiv2_organization_list_workspaces', {id: props.organization.id}],
            autoload: props.organization.id && !props.new
          }}
          primaryAction={workspacesSource.parameters.primaryAction}
          delete={{
            url: ['apiv2_organization_remove_workspaces', {id: props.organization.id}]
          }}
          definition={workspacesSource.parameters.definition}
          card={workspacesSource.parameters.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        disabled={props.new}
        actions={[
          {
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            modal: [MODAL_USERS, {
              url: ['apiv2_user_list_managed'],
              selectAction: (users) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.organization.id, users.map(user => user.id))
              })
            }]
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
            name: 'add',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            modal: [MODAL_GROUPS, {
              url: ['apiv2_group_list_managed'],
              selectAction: (groups) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addGroups(props.organization.id, groups.map(group => group.id))
              })
            }]
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
    </FormSections>
  </FormData>

OrganizationForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  organization: T.shape({
    id: T.string
  }).isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  addWorkspaces: T.func.isRequired,
  addManagers: T.func.isRequired,
  updateProp: T.func.isRequired
}

const Organization = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelectors.isNew(formSelectors.form(state, baseSelectors.STORE_NAME+'.organizations.current')),
    organization: formSelectors.data(formSelectors.form(state, baseSelectors.STORE_NAME+'.organizations.current'))
  }),
  dispatch => ({
    updateProp(name, value) {
      dispatch(formActions.updateProp(baseSelectors.STORE_NAME+'.organizations.current', name, value))
    },
    addUsers(organizationId, users) {
      dispatch(actions.addUsers(organizationId, users))
    },
    addManagers(organizationId, users) {
      dispatch(actions.addManagers(organizationId, users))
    },
    addGroups(organizationId, groups) {
      dispatch(actions.addGroups(organizationId, groups))
    },
    addWorkspaces(organizationId, workspaces) {
      dispatch(actions.addWorkspaces(organizationId, workspaces))
    }
  })
)(OrganizationForm)

export {
  Organization
}
