import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {t, trans} from '#/main/core/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox'
import {selectors as formSelect} from '#/main/app/content/form/store/selectors'
import {actions as formActions} from '#/main/app/content/form/store/actions'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'

import {enumRole, PLATFORM_ROLE} from '#/main/core/user/role/constants'
import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {actions} from '#/main/core/administration/user/role/actions'
import {GroupList} from '#/main/core/administration/user/group/components/group-list'
import {UserList} from '#/main/core/administration/user/user/components/user-list'

const RoleForm = props =>
  <FormData
    level={3}
    name="roles.current"
    buttons={true}
    target={(role, isNew) => isNew ?
      ['apiv2_role_create'] :
      ['apiv2_role_update', {id: role.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: '/roles',
      exact: true
    }}
    sections={[
      {
        title: t('general'),
        primary: true,
        fields: [
          {
            name: 'translationKey',
            type: 'translation',
            label: t('name'),
            required: true,
            disabled: props.role.meta && props.role.meta.readOnly
          }, {
            name: 'type',
            type: 'choice',
            label: t('type'),
            readOnly: true,
            options: {
              condensed: true,
              choices: enumRole
            }
          }
        ],
        advanced: {
          fields: [
            {
              name: 'meta.personalWorkspaceCreation',
              type: 'boolean',
              label: t('role_personalWorkspaceCreation'),
              help: t('role_personalWorkspaceCreation_help')
            }
          ]
        }
      }, {
        icon: 'fa fa-fw fa-key',
        title: t('access_restrictions'),
        fields: [
          {
            name: 'access_max_users',
            type: 'boolean',
            label: t('access_max_users'),
            calculated: (role) => role.restrictions && null !== role.restrictions.maxUsers && '' !== role.restrictions.maxUsers,
            onChange: checked => {
              if (checked) {
                // initialize with the current nb of users with the role
                props.updateProp('restrictions.maxUsers', props.role.meta.users || 0)
              } else {
                // reset max users field
                props.updateProp('restrictions.maxUsers', null)
              }
            },
            linked: [
              {
                name: 'restrictions.maxUsers',
                type: 'number',
                label: t('maxUsers'),
                displayed: props.role.restrictions && null !== props.role.restrictions.maxUsers && '' !== props.role.restrictions.maxUsers,
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
      {PLATFORM_ROLE === props.role.type &&
        <FormSection
          icon="fa fa-fw fa-cogs"
          title={t('administration_tools')}
        >
          <div className="list-group" fill={true}>
            {Object.keys(props.role.adminTools || {}).map(toolName =>
              <Checkbox
                key={toolName}
                id={toolName}
                className={classes('list-group-item', {
                  'list-group-item-selected': props.role.adminTools[toolName]
                })}
                label={trans(toolName, {}, 'tools')}
                checked={props.role.adminTools[toolName]}
                onChange={checked => props.updateProp(`adminTools.${toolName}`, checked)}
              />
            )}
          </div>
        </FormSection>
      }

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={t('users')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: t('add_user'),
            callback: () => props.pickUsers(props.role.id),
            disabled: props.role.restrictions && null !== props.role.restrictions.maxUsers && props.role.restrictions.maxUsers <= props.role.meta.users
          }
        ]}
      >
        <ListData
          name="roles.current.users"
          fetch={{
            url: ['apiv2_role_list_users', {id: props.role.id}],
            autoload: props.role.id && !props.new
          }}
          primaryAction={UserList.open}
          delete={{
            url: ['apiv2_role_remove_users', {id: props.role.id}]
          }}
          definition={UserList.definition}
          card={UserList.card}
        />
      </FormSection>

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-id-badge"
        title={t('groups')}
        disabled={props.new}
        actions={[
          {
            type: CALLBACK_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: t('add_group'),
            callback: () => props.pickGroups(props.role.id)
          }
        ]}
      >
        <ListData
          name="roles.current.groups"
          fetch={{
            url: ['apiv2_role_list_groups', {id: props.role.id}],
            autoload: props.role.id && !props.new
          }}
          primaryAction={GroupList.open}
          delete={{
            url: ['apiv2_role_remove_groups', {id: props.role.id}]
          }}
          definition={GroupList.definition}
          card={GroupList.card}
        />
      </FormSection>
    </FormSections>
  </FormData>

RoleForm.propTypes = {
  new: T.bool.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired
}

const Role = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'roles.current')),
    role: formSelect.data(formSelect.form(state, 'roles.current'))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('roles.current', propName, propValue))
    },
    pickUsers(roleId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: t('add_users'),
        confirmText: t('add'),
        name: 'users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addUsers(roleId, selected))
      }))
    },
    pickGroups(roleId){
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-users',
        title: t('add_groups'),
        confirmText: t('add'),
        name: 'groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addGroups(roleId, selected))
      }))
    }
  })
)(RoleForm)

export {
  Role
}
