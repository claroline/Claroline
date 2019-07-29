import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {CALLBACK_BUTTON, LINK_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {actions} from '#/main/core/tools/users/role/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {selectors} from '#/main/core/tools/users/store'
import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {GroupList} from '#/main/core/administration/users/group/components/group-list'
import {UserList} from '#/main/core/administration/users/user/components/user-list'

const ToolRightsRow = props =>
  <div className="tool-rights-row list-group-item">
    <div className="tool-rights-title">
      {trans(props.toolName, {}, 'tools')}
    </div>
    <div className="tool-rights-actions">
      <Checkbox
        key={`${props.toolName}-open`}
        id={`${props.toolName}-open`}
        label={trans('open')}
        checked={props.canOpen}
        onChange={checked => props.updateOpen(checked)}
      />
      <Checkbox
        key={`${props.toolName}-edit`}
        id={`${props.toolName}-edit`}
        label={trans('edit')}
        checked={props.canEdit}
        onChange={checked => props.updateEdit(checked)}
      />
    </div>
  </div>

ToolRightsRow.propTypes = {
  toolName: T.string.isRequired,
  canOpen: T.bool.isRequired,
  canEdit: T.bool.isRequired,
  updateOpen: T.func.isRequired,
  updateEdit: T.func.isRequired
}

const RoleForm = props =>
  <FormData
    level={3}
    name={selectors.STORE_NAME + '.roles.current'}
    buttons={true}
    target={(role, isNew) => isNew ?
      ['apiv2_role_create', {options: ['serialize_role_tools_rights', `workspace_id_${props.workspaceId}`]}] :
      ['apiv2_role_update', {id: role.id, options: ['serialize_role_tools_rights', `workspace_id_${props.workspaceId}`]}]
    }
    cancel={{
      type: LINK_BUTTON,
      target: `${props.path}/roles`,
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
          }
        ]
      }
    ]}
  >
    <FormSections level={3}>
      <FormSection
        className="embedded-list-section"
        icon="fa fa-fw fa-cogs"
        title={trans('tools')}
        disabled={props.new}
      >
        <div className="list-group" fill={true}>
          {Object.keys(props.role.tools || {}).map(toolName =>
            <ToolRightsRow
              key={`tool-rights-${toolName}`}
              toolName={toolName}
              canOpen={props.role.tools[toolName]['open']}
              canEdit={props.role.tools[toolName]['edit']}
              updateOpen={checked => props.updateProp(`tools.${toolName}.open`, checked)}
              updateEdit={checked => props.updateProp(`tools.${toolName}.edit`, checked)}
            />
          )}
        </div>
      </FormSection>

      {-1 === ['ROLE_ANONYMOUS', 'ROLE_USER'].indexOf(props.role.name) &&
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
              callback: () => props.pickUsers(props.role.id),
              disabled: props.role.restrictions && null !== props.role.restrictions.maxUsers && props.role.restrictions.maxUsers <= props.role.meta.users
            }
          ]}
        >
          <ListData
            name={selectors.STORE_NAME + '.roles.current.users'}
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
      }

      {-1 === ['ROLE_ANONYMOUS', 'ROLE_USER'].indexOf(props.role.name) &&
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-id-badge"
          title={trans('groups')}
          disabled={props.new}
          actions={[
            {
              type: CALLBACK_BUTTON,
              icon: 'fa fa-fw fa-plus',
              label: trans('add_group'),
              callback: () => props.pickGroups(props.role.id)
            }
          ]}
        >
          <ListData
            name={selectors.STORE_NAME + '.roles.current.groups'}
            primaryAction={GroupList.open}
            fetch={{
              url: ['apiv2_role_list_groups', {id: props.role.id}],
              autoload: props.role.id && !props.new
            }}
            delete={{
              url: ['apiv2_role_remove_groups', {id: props.role.id}]
            }}
            definition={GroupList.definition}
            card={GroupList.card}
          />
        </FormSection>
      }
    </FormSections>
  </FormData>

RoleForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  role: T.shape(RoleTypes.propTypes).isRequired,
  workspaceId: T.string.isRequired,
  updateProp: T.func.isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired
}

const Role = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.roles.current')),
    role: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.roles.current')),
    path: toolSelectors.path(state),
    workspaceId: toolSelectors.contextData(state) ? toolSelectors.contextData(state).uuid : null
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp(selectors.STORE_NAME + '.roles.current', propName, propValue))
    },
    pickUsers(roleId) {
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-user',
        title: trans('add_users'),
        confirmText: trans('add'),
        name: selectors.STORE_NAME + '.users.picker',
        definition: UserList.definition,
        card: UserList.card,
        fetch: {
          url: ['apiv2_user_list_registerable'],
          autoload: true
        },
        handleSelect: (selected) => dispatch(actions.addUsers(roleId, selected))
      }))
    },
    pickGroups(roleId){
      dispatch(modalActions.showModal(MODAL_DATA_LIST, {
        icon: 'fa fa-fw fa-users',
        title: trans('add_groups'),
        confirmText: trans('add'),
        name: selectors.STORE_NAME + '.groups.picker',
        definition: GroupList.definition,
        card: GroupList.card,
        fetch: {
          url: ['apiv2_group_list_registerable'],
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
