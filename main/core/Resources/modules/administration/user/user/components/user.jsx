import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'

import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {ListData} from '#/main/app/content/list/containers/data'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {actions} from '#/main/core/administration/user/user/actions'

import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list'
import {GroupList} from '#/main/core/administration/user/group/components/group-list'
import {RoleList} from '#/main/core/administration/user/role/components/role-list'

const UserForm = props =>
  <FormData
    level={3}
    name="users.current"
    buttons={true}
    target={(user, isNew) => isNew ?
      ['apiv2_user_create'] :
      ['apiv2_user_update', {id: user.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: '/users',
      exact: true
    }}
    sections={[
      {
        title: t('general'),
        primary: true,
        fields: [
          {
            name: 'lastName',
            type: 'string',
            label: t('last_name'),
            required: true
          }, {
            name: 'firstName',
            type: 'string',
            label: t('first_name'),
            required: true
          }, {
            name: 'email',
            type: 'email',
            label: t('email'),
            required: true
          }, {
            name: 'username',
            type: 'username',
            label: t('username'),
            required: true
          }, {
            name: 'plainPassword',
            type: 'password',
            label: t('password'),
            displayed: props.new,
            required: true,
            options: {
              autoComplete: 'new-password'
            }
          },
          {
            name: 'mainOrganization',
            type: 'organization',
            label: t('main_organization')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-info',
        title: t('information'),
        fields: [
          {
            name: 'administrativeCode',
            type: 'string',
            label: t('administrativeCode')
          }, {
            name: 'meta.description',
            type: 'html',
            label: t('description')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-desktop',
        title: t('display_parameters'),
        fields: [
          {
            name: 'meta.locale',
            type: 'locale',
            label: t('language'),
            required: true,
            options: {
              onlyEnabled: true
            }
          }, {
            name: 'picture',
            type: 'image',
            label: t('picture')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: t('access_restrictions'),
        fields: [
          {
            name: 'restrictions.disabled',
            type: 'boolean',
            label: t('disable_user')
          }, {
            name: 'restrictions.enableDates',
            type: 'boolean',
            label: t('restrict_by_dates'),
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
                label: t('access_dates'),
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
        title={t('groups')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: t('add_groups'),
            callback: () => props.pickGroups(props.user.id)
          }
        ]}
      >
        <ListData
          name="users.current.groups"
          fetch={{
            url: ['apiv2_user_list_groups', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          primaryAction={GroupList.open}
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
        title={t('organizations')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: t('add_organizations'),
            callback: () => props.pickOrganizations(props.user.id)
          }
        ]}
      >
        <ListData
          name="users.current.organizations"
          fetch={{
            url: ['apiv2_user_list_organizations', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          primaryAction={OrganizationList.open}
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
        title={t('roles')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: t('add_roles'),
            callback: () => props.pickRoles(props.user.id)
          }
        ]}
      >
        <ListData
          name="users.current.roles"
          fetch={{
            url: ['apiv2_user_list_roles', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          primaryAction={RoleList.open}
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
  new: T.bool.isRequired,
  user: T.shape({
    id: T.string,
    restrictions: T.shape({
      dates: T.arrayOf(T.string).isRequired
    })
  }).isRequired,
  updateProp: T.func.isRequired,
  pickGroups: T.func.isRequired,
  pickOrganizations: T.func.isRequired,
  pickRoles: T.func.isRequired
}

const User = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'users.current')),
    user: formSelect.data(formSelect.form(state, 'users.current'))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('users.current', propName, propValue))
    },
    pickGroups(userId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-users',
        title: t('add_groups'),
        name: 'groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addGroups(userId, selected))
      }))
    },
    pickOrganizations(userId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-building',
        title: t('add_organizations'),
        confirmText: t('add'),
        name: 'organizations.picker',
        definition: OrganizationList.definition,
        card: OrganizationList.card,
        fetch: {
          url: ['apiv2_organization_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addOrganizations(userId, selected))
      }))
    },
    pickRoles(userId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-id-badge',
        title: t('add_roles'),
        name: 'roles.picker',
        definition: RoleList.definition,
        card: RoleList.card,
        fetch: {
          url: ['apiv2_role_platform_grantable_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addRoles(userId, selected))
      }))
    }
  })
)(UserForm)

export {
  User
}
