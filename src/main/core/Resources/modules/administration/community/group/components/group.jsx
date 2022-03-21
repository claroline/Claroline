import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {actions} from '#/main/core/administration/community/group/store'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_ROLES} from '#/main/core/modals/roles'
import {MODAL_ORGANIZATIONS} from '#/main/core/modals/organizations'
import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'
import {RoleList} from '#/main/core/administration/community/role/components/role-list'
import {UserList} from '#/main/core/user/components/list'

const GroupForm = props =>
  <FormData
    level={3}
    name={`${baseSelectors.STORE_NAME}.groups.current`}
    buttons={true}
    target={(group, isNew) => isNew ?
      ['apiv2_group_create'] :
      ['apiv2_group_update', {id: group.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/groups',
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
        icon="fa fa-fw fa-user"
        title={trans('users')}
        disabled={!props.group.id || props.new}
        actions={[
          {
            name: 'add-users',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_users'),
            modal: [MODAL_USERS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.group.id, selected)
              })
            }]
          }
        ]}
      >
        {props.group.id && !props.new &&
          <UserList
            name={`${baseSelectors.STORE_NAME}.groups.current.users`}
            url={['apiv2_group_list_users', {id: props.group.id}]}
            primaryAction={(row) => ({
              type: LINK_BUTTON,
              target: `${props.path}/users/form/${row.id}`,
              label: trans('edit', {}, 'actions')
            })}
            delete={{
              url: ['apiv2_group_remove_users', {id: props.group.id}]
            }}
          />
        }
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-id-badge"
        title={trans('roles')}
        disabled={props.new}
        actions={[
          {
            name: 'add-roles',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_roles'),
            modal: [MODAL_ROLES, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addRoles(props.group.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.groups.current.roles`}
          fetch={{
            url: ['apiv2_group_list_roles', {id: props.group.id}],
            autoload: props.group.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/roles/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_group_remove_roles', {id: props.group.id}]
          }}
          definition={RoleList.definition}
          card={RoleList.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-building"
        title={trans('organizations')}
        disabled={props.new}
        actions={[
          {
            name: 'add-organizations',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_organizations'),
            modal: [MODAL_ORGANIZATIONS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addOrganizations(props.group.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.groups.current.organizations`}
          fetch={{
            url: ['apiv2_group_list_organizations', {id: props.group.id}],
            autoload: props.group.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/organizations/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_group_remove_organizations', {id: props.group.id}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

GroupForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  group: T.shape({
    id: T.string
  }).isRequired,
  addUsers: T.func.isRequired,
  addRoles: T.func.isRequired,
  addOrganizations: T.func.isRequired
}

const Group = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.groups.current')),
    group: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.groups.current'))
  }),
  dispatch =>({
    addUsers(groupId, selected) {
      dispatch(actions.addUsers(groupId, selected.map(row => row.id)))
    },
    addRoles(groupId, selected) {
      dispatch(actions.addRoles(groupId, selected.map(row => row.id)))
    },
    addOrganizations(groupId, selected) {
      dispatch(actions.addOrganizations(groupId, selected.map(row => row.id)))
    }
  })
)(GroupForm)

export {
  Group
}
