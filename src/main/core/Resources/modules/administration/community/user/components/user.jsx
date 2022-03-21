import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'

import {selectors as configSelectors} from '#/main/app/config/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {actions} from '#/main/core/administration/community/user/store'

import {OrganizationList} from '#/main/core/administration/community/organization/components/organization-list'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {RoleList} from '#/main/core/administration/community/role/components/role-list'
import {MODAL_GROUPS} from '#/main/core/modals/groups'
import {MODAL_ROLES} from '#/main/core/modals/roles'
import {MODAL_ORGANIZATIONS} from '#/main/core/modals/organizations'

const UserForm = props =>
  <FormData
    level={3}
    name={`${baseSelectors.STORE_NAME}.users.current`}
    buttons={true}
    target={(user, isNew) => isNew ?
      ['apiv2_user_create'] :
      ['apiv2_user_update', {id: user.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/users',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'lastName',
            type: 'string',
            label: trans('last_name'),
            required: true
          }, {
            name: 'firstName',
            type: 'string',
            label: trans('first_name'),
            required: true
          }, {
            name: 'email',
            type: 'email',
            label: trans('email'),
            required: true
          }, {
            name: 'username',
            type: 'username',
            label: trans('username'),
            displayed: props.username,
            required: true
          }, {
            name: 'plainPassword',
            type: 'password',
            label: trans('password'),
            displayed: props.new,
            required: true,
            options: {
              autoComplete: 'new-password'
            }
          }, {
            name: 'mainOrganization',
            type: 'organization',
            required: true,
            label: trans('main_organization')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: trans('information'),
        fields: [
          {
            name: 'phone',
            type: 'string',
            label: trans('phone')
          }, {
            name: 'administrativeCode',
            type: 'string',
            label: trans('administrativeCode')
          }, {
            name: 'meta.description',
            type: 'html',
            label: trans('description')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: trans('display_parameters'),
        fields: [
          {
            name: 'meta.locale',
            type: 'locale',
            label: trans('language'),
            required: true,
            options: {
              onlyEnabled: true
            }
          }, {
            name: 'picture',
            type: 'image',
            label: trans('picture')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'restrictions.disabled',
            type: 'boolean',
            label: trans('disable_user')
          }, {
            name: 'restrictions.enableDates',
            type: 'boolean',
            label: trans('restrict_by_dates'),
            calculated: (user) => user.restrictions && 0 !== user.restrictions.dates.length,
            onChange: activated => {
              if (!activated) {
                props.updateProp('restrictions.dates', [])
              } else {
                props.updateProp('restrictions.dates', [null, null])
              }
            },
            linked: [
              {
                name: 'restrictions.dates',
                type: 'date-range',
                label: trans('access_dates'),
                displayed: props.user.restrictions && 0!== props.user.restrictions.dates.length,
                required: true,
                options: {
                  time: true
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
        icon="fa fa-fw fa-users"
        title={trans('groups')}
        disabled={props.new}
        actions={[
          {
            name: 'add-groups',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_groups'),
            modal: [MODAL_GROUPS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addGroups(props.user.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.users.current.groups`}
          fetch={{
            url: ['apiv2_user_list_groups', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/groups/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_user_remove_groups', {id: props.user.id}]
          }}
          definition={GroupList.definition}
          card={GroupList.card}
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
                callback: () => props.addOrganizations(props.user.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.users.current.organizations`}
          fetch={{
            url: ['apiv2_user_list_organizations', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/organizations/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_user_remove_organizations', {id: props.user.id}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
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
                callback: () => props.addRoles(props.user.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.users.current.roles`}
          fetch={{
            url: ['apiv2_user_list_roles', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/roles/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
          delete={{
            url: ['apiv2_user_remove_roles', {id: props.user.id}]
          }}
          definition={RoleList.definition}
          card={RoleList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

UserForm.propTypes = {
  path: T.string.isRequired,
  username: T.bool.isRequired,
  new: T.bool.isRequired,
  user: T.shape({
    id: T.string,
    restrictions: T.shape({
      dates: T.arrayOf(T.string).isRequired
    })
  }).isRequired,
  updateProp: T.func.isRequired,
  addGroups: T.func.isRequired,
  addOrganizations: T.func.isRequired,
  addRoles: T.func.isRequired
}

const User = connect(
  state => ({
    path: toolSelectors.path(state),
    username: configSelectors.param(state, 'community.username'),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.users.current')),
    user: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.users.current'))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(baseSelectors.STORE_NAME+'.users.current', propName, propValue))
    },
    addGroups(userId, selected) {
      dispatch(actions.addGroups(userId, selected.map(row => row.id)))
    },
    addOrganizations(userId, selected) {
      dispatch(actions.addOrganizations(userId, selected.map(row => row.id)))
    },
    addRoles(userId, selected) {
      dispatch(actions.addRoles(userId, selected.map(row => row.id)))
    }
  })
)(UserForm)

export {
  User
}
