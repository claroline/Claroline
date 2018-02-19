import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {t} from '#/main/core/translation'

import {actions as modalActions} from '#/main/core/layout/modal/actions'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {FormContainer} from '#/main/core/data/form/containers/form.jsx'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections.jsx'
import {actions as formActions} from '#/main/core/data/form/actions'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {DataListContainer} from '#/main/core/data/list/containers/data-list.jsx'

import {actions} from '#/main/core/administration/user/user/actions'

import {OrganizationList} from '#/main/core/administration/user/organization/components/organization-list.jsx'
import {GroupList} from '#/main/core/administration/user/group/components/group-list.jsx'
import {RoleList} from '#/main/core/administration/user/role/components/role-list.jsx'

const UserForm = props =>
  <FormContainer
    level={3}
    name="users.current"
    sections={[
      {
        id: 'general',
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
            required: true
          },
          {
            name: 'mainOrganization',
            type: 'organization',
            label: t('main_organization')
          }
        ]
      }, {
        id: 'information',
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
        id: 'display_parameters',
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
        id: 'restrictions',
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
            calculated: props.user.restrictions && 0!== props.user.restrictions.dates.length,
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
        id="user-groups"
        className="embedded-list-section"
        icon="fa fa-fw fa-users"
        title={t('groups')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_groups'),
            action: () => props.pickGroups(props.user.id)
          }
        ]}
      >
        <DataListContainer
          name="users.current.groups"
          open={GroupList.open}
          fetch={{
            url: ['apiv2_user_list_groups', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          delete={{
            url: ['apiv2_user_remove_groups', {id: props.user.id}]
          }}
          definition={GroupList.definition}
          card={GroupList.card}
        />
      </FormSection>

      <FormSection
        id="group-organizations"
        className="embedded-list-section"
        icon="fa fa-fw fa-building"
        title={t('organizations')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_organizations'),
            action: () => props.pickOrganizations(props.user.id)
          }
        ]}
      >
        <DataListContainer
          name="users.current.organizations"
          open={OrganizationList.open}
          fetch={{
            url: ['apiv2_user_list_organizations', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          delete={{
            url: ['apiv2_user_remove_organizations', {id: props.user.id}]
          }}
          definition={OrganizationList.definition}
          card={OrganizationList.card}
        />
      </FormSection>

      <FormSection
        id="user-roles"
        className="embedded-list-section"
        icon="fa fa-fw fa-id-badge"
        title={t('roles')}
        disabled={props.new}
        actions={[
          {
            icon: 'fa fa-fw fa-plus',
            label: t('add_roles'),
            action: () => props.pickRoles(props.user.id)
          }
        ]}
      >
        <DataListContainer
          name="users.current.roles"
          open={RoleList.open}
          fetch={{
            url: ['apiv2_user_list_roles', {id: props.user.id}],
            autoload: props.user.id && !props.new
          }}
          delete={{
            url: ['apiv2_user_remove_roles', {id: props.user.id}]
          }}
          definition={RoleList.definition}
          card={RoleList.card}
        />
      </FormSection>
    </FormSections>
  </FormContainer>

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
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-users',
        title: t('add_groups'),
        name: 'groups.picker',
        open: GroupList.open,
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
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
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
      dispatch(modalActions.showModal(MODAL_DATA_PICKER, {
        icon: 'fa fa-fw fa-id-badge',
        title: t('add_roles'),
        name: 'roles.picker',
        open: RoleList.open,
        definition: RoleList.definition,
        card: RoleList.card,
        fetch: {
          url: ['apiv2_role_list'],
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
