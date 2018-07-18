import React from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/core/translation'

import {FormContainer} from '#/main/core/data/form/containers/form'
import {FormSections, FormSection} from '#/main/core/layout/form/components/form-sections'
import {DataListContainer} from '#/main/core/data/list/containers/data-list'
import {select as formSelect} from '#/main/core/data/form/selectors'
import {actions as formActions} from '#/main/core/data/form/actions'
import {actions as modalActions} from '#/main/app/overlay/modal/store'
import {select as workspaceSelect} from '#/main/core/workspace/selectors'
import {MODAL_DATA_PICKER} from '#/main/core/data/list/modals'
import {Checkbox} from '#/main/core/layout/form/components/field/checkbox'

import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {actions} from '#/main/core/administration/user/role/actions'
import {GroupList} from '#/main/core/administration/user/group/components/group-list'
import {UserList} from '#/main/core/administration/user/user/components/user-list'

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
  <FormContainer
    level={3}
    name="roles.current"
    buttons={true}
    target={(role, isNew) => isNew ?
      ['apiv2_role_create', {options: ['serialize_role_tools_rights', `workspace_id_${props.workspaceId}`]}] :
      ['apiv2_role_update', {id: role.id, options: ['serialize_role_tools_rights', `workspace_id_${props.workspaceId}`]}]
    }
    cancel={{
      type: 'link',
      target: '/roles',
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
              type: 'callback',
              icon: 'fa fa-fw fa-plus',
              label: trans('add_user'),
              callback: () => props.pickUsers(props.role.id),
              disabled: props.role.restrictions && null !== props.role.restrictions.maxUsers && props.role.restrictions.maxUsers <= props.role.meta.users
            }
          ]}
        >
          <DataListContainer
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
      }

      {-1 === ['ROLE_ANONYMOUS', 'ROLE_USER'].indexOf(props.role.name) &&
        <FormSection
          className="embedded-list-section"
          icon="fa fa-fw fa-id-badge"
          title={trans('groups')}
          disabled={props.new}
          actions={[
            {
              type: 'callback',
              icon: 'fa fa-fw fa-plus',
              label: trans('add_group'),
              callback: () => props.pickGroups(props.role.id)
            }
          ]}
        >
          <DataListContainer
            name="roles.current.groups"
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
  </FormContainer>
RoleForm.propTypes = {
  new: T.bool.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ).isRequired,
  updateProp: T.func.isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired
}

RoleForm.propTypes = {
  new: T.bool.isRequired,
  role: T.shape(
    RoleTypes.propTypes
  ).isRequired,
  workspaceId: T.string.isRequired,
  updateProp: T.func.isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired
}

const Role = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, 'roles.current')),
    role: formSelect.data(formSelect.form(state, 'roles.current')),
    workspaceId: workspaceSelect.workspace(state).uuid
  }),
  dispatch => ({
    updateProp(propName, propValue) {
      dispatch(formActions.updateProp('roles.current', propName, propValue))
    },
    pickUsers(roleId) {
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
        handleSelect: (selected) => dispatch(actions.addUsers(roleId, selected))
      }))
    },
    pickGroups(roleId){
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
        handleSelect: (selected) => dispatch(actions.addGroups(roleId, selected))
      }))
    }
  })
)(RoleForm)

export {
  Role
}
