import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'
import classes from 'classnames'

import {trans} from '#/main/app/intl/translation'

import {FormData} from '#/main/app/content/form/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {ListData} from '#/main/app/content/list/containers/data'
import {Checkbox} from '#/main/app/input/components/checkbox'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {selectors as toolSelectors} from '#/main/core/tool/store'

import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'
import {selectors as baseSelectors} from '#/main/core/administration/community/store'
import {constants} from '#/main/core/user/constants'
import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {actions} from '#/main/core/administration/community/role/store'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {UserList} from '#/main/core/administration/community/user/components/user-list'

// TODO : merge with main/core/tools/community/role/components/role

const RoleForm = props =>
  <FormData
    level={3}
    name={`${baseSelectors.STORE_NAME}.roles.current`}
    buttons={true}
    target={(role, isNew) => isNew ?
      ['apiv2_role_create'] :
      ['apiv2_role_update', {id: role.id}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: props.path+'/roles',
      exact: true
    }}
    sections={[
      {
        title: trans('general'),
        primary: true,
        fields: [
          {
            name: 'translationKey',
            type: 'translation',
            label: trans('name'),
            required: true,
            disabled: props.role.meta && props.role.meta.readOnly
          }, {
            name: 'type',
            type: 'choice',
            label: trans('type'),
            disabled: !props.new,
            required: true,
            options: {
              condensed: true,
              choices: constants.ROLE_TYPES
            },
            onChange: (value) => {
              if (constants.ROLE_WORKSPACE !== value) {
                props.updateProp('workspace', null)
              }

              if (constants.ROLE_USER !== props.role.type) {
                props.updateProp('user', null)
              }
            },
            linked: [
              {
                name: 'workspace',
                type: 'workspace',
                label: trans('workspace'),
                required: true,
                disabled: !props.new,
                displayed: constants.ROLE_WORKSPACE === props.role.type
              }, {
                name: 'user',
                type: 'user',
                label: trans('user'),
                required: true,
                disabled: !props.new,
                displayed: constants.ROLE_USER === props.role.type
              }
            ]
          }
        ]
      }, {
        icon: 'fa fa-fw fa-book',
        title: trans('workspace'),
        fields: [
          {
            name: 'meta.personalWorkspaceCreationEnabled',
            type: 'boolean',
            label: trans('role_personalWorkspaceCreation'),
            help: trans('role_personalWorkspaceCreation_help')
          }
        ]
      }, {
        icon: 'fa fa-fw fa-key',
        title: trans('access_restrictions'),
        fields: [
          {
            name: 'access_max_users',
            type: 'boolean',
            label: trans('access_max_users'),
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
                label: trans('maxUsers'),
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
      {constants.ROLE_PLATFORM === props.role.type &&
        <FormSection
          icon="fa fa-fw fa-cogs"
          title={trans('administration_tools')}
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

      {constants.ROLE_PLATFORM === props.role.type &&
        <FormSection
          icon="fa fa-fw fa-tools"
          title={trans('desktop_tools')}
        >
          <div className="list-group" fill={true}>
            {Object.keys(props.role.desktopTools || {}).map(toolName =>
              <div key={toolName} className="tool-rights-row list-group-item">
                <div className="tool-rights-title">
                  {trans(toolName, {}, 'tools')}
                </div>

                <div className="tool-rights-actions">
                  {Object.keys(props.role.desktopTools[toolName]).map((permName) =>
                    <Checkbox
                      key={permName}
                      id={`${toolName}-${permName}`}
                      label={trans(permName, {}, 'actions')}
                      checked={props.role.desktopTools[toolName][permName]}
                      onChange={checked => props.updateProp(`desktopTools.${toolName}.${permName}`, checked)}
                    />
                  )}
                </div>
              </div>
            )}
          </div>
        </FormSection>
      }

      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-user"
        title={trans('users')}
        disabled={props.new}
        actions={[
          {
            name: 'add-users',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_user'),
            disabled: props.role.restrictions && null !== props.role.restrictions.maxUsers && props.role.restrictions.maxUsers <= props.role.meta.users,
            modal: [MODAL_USERS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addUsers(props.role.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.roles.current.users`}
          fetch={{
            url: ['apiv2_role_list_users', {id: props.role.id}],
            autoload: props.role.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/users/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
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
        title={trans('groups')}
        disabled={props.new}
        actions={[
          {
            name: 'add-groups',
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_group'),
            modal: [MODAL_GROUPS, {
              selectAction: (selected) => ({
                type: CALLBACK_BUTTON,
                label: trans('add', {}, 'actions'),
                callback: () => props.addGroups(props.role.id, selected)
              })
            }]
          }
        ]}
      >
        <ListData
          name={`${baseSelectors.STORE_NAME}.roles.current.groups`}
          fetch={{
            url: ['apiv2_role_list_groups', {id: props.role.id}],
            autoload: props.role.id && !props.new
          }}
          primaryAction={(row) => ({
            type: LINK_BUTTON,
            target: `${props.path}/groups/form/${row.id}`,
            label: trans('edit', {}, 'actions')
          })}
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
  path: T.string.isRequired,
  new: T.bool.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired
}

const Role = connect(
  state => ({
    path: toolSelectors.path(state),
    new: formSelect.isNew(formSelect.form(state, baseSelectors.STORE_NAME+'.roles.current')),
    role: formSelect.data(formSelect.form(state, baseSelectors.STORE_NAME+'.roles.current'))
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(baseSelectors.STORE_NAME+'.roles.current', propName, propValue))
    },
    addUsers(roleId, selected) {
      dispatch(actions.addUsers(roleId, selected.map(row => row.id)))
    },
    addGroups(roleId, selected) {
      dispatch(actions.addGroups(roleId, selected.map(row => row.id)))
    }
  })
)(RoleForm)

export {
  Role
}
