import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import {connect} from 'react-redux'

import {trans} from '#/main/app/intl/translation'
import {actions as formActions, selectors as formSelect} from '#/main/app/content/form/store'
import {actions as modalActions} from '#/main/app/overlays/modal/store'
import {MODAL_DATA_LIST} from '#/main/app/modals/list'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {actions} from '#/main/core/tools/community/role/store'
import {selectors as toolSelectors} from '#/main/core/tool/store'
import {
  actions as workspaceActions,
  selectors as workspaceSelectors
} from '#/main/core/workspace/store'
import {getActions as getWorkspaceActions} from '#/main/core/workspace/utils'
import {MODAL_WORKSPACE_SHORTCUTS} from '#/main/core/workspace/modals/shortcuts'
import {constants as workspaceConstants} from '#/main/core/workspace/constants'
import {selectors} from '#/main/core/tools/community/store'
import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {UserList} from '#/main/core/administration/community/user/components/user-list'

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

const ShortcutRow = props =>
  <div className="tool-rights-row list-group-item">
    <div className="tool-rights-title">
      {props.label}
    </div>
    <div className="tool-rights-actions">
      <Button
        className="btn btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash-o"
        label={trans('delete', {}, 'actions')}
        dangerous={true}
        callback={() => props.removeShortcut()}
        tooltip="left"
      />
    </div>
  </div>

ShortcutRow.propTypes = {
  name: T.string.isRequired,
  label: T.string.isRequired,
  type: T.string.isRequired,
  removeShortcut: T.func.isRequired
}

class RoleForm extends Component {
  constructor(props) {
    super(props)

    this.state = {
      actions: []
    }
  }

  componentDidMount() {
    if (this.props.workspace) {
      getWorkspaceActions([this.props.workspace], {}, '', null).then((actions) => this.setState({actions: actions}))
    }
  }

  getLabel(type, name) {
    let action = null

    switch (type) {
      case 'tool':
        return trans(name, {}, 'tools')
      case 'action':
        action = this.state.actions.find(a => a.name === name)

        return action ? action.label : name
      default:
        return name
    }
  }

  render() {
    return (
      <FormData
        level={3}
        name={selectors.STORE_NAME + '.roles.current'}
        buttons={true}
        target={(role, isNew) => isNew ?
          ['apiv2_role_create', {options: ['serialize_role_tools_rights', `workspace_id_${this.props.workspace.uuid}`]}] :
          ['apiv2_role_update', {id: role.id, options: ['serialize_role_tools_rights', `workspace_id_${this.props.workspace.uuid}`]}]
        }
        cancel={{
          type: LINK_BUTTON,
          target: `${this.props.path}/roles`,
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
                disabled: this.props.role.meta && this.props.role.meta.readOnly
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
            disabled={this.props.new}
          >
            <div className="list-group" fill={true}>
              {Object.keys(this.props.role.tools || {}).map(toolName =>
                <ToolRightsRow
                  key={`tool-rights-${toolName}`}
                  toolName={toolName}
                  canOpen={this.props.role.tools[toolName]['open']}
                  canEdit={this.props.role.tools[toolName]['edit']}
                  updateOpen={checked => this.props.updateProp(`tools.${toolName}.open`, checked)}
                  updateEdit={checked => this.props.updateProp(`tools.${toolName}.edit`, checked)}
                />
              )}
            </div>
          </FormSection>

          {-1 === ['ROLE_ANONYMOUS', 'ROLE_USER'].indexOf(this.props.role.name) &&
            <FormSection
              className="embedded-list-section"
              icon="fa fa-fw fa-user"
              title={trans('users')}
              disabled={this.props.new}
              actions={[
                {
                  name: 'add',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_user'),
                  callback: () => this.props.pickUsers(this.props.role.id),
                  disabled: this.props.role.restrictions && null !== this.props.role.restrictions.maxUsers && this.props.role.restrictions.maxUsers <= this.props.role.meta.users
                }
              ]}
            >
              <ListData
                name={selectors.STORE_NAME + '.roles.current.users'}
                fetch={{
                  url: ['apiv2_role_list_users', {id: this.props.role.id}],
                  autoload: this.props.role.id && !this.props.new
                }}
                primaryAction={UserList.open}
                delete={{
                  url: ['apiv2_role_remove_users', {id: this.props.role.id}]
                }}
                definition={UserList.definition}
                card={UserList.card}
              />
            </FormSection>
          }

          {-1 === ['ROLE_ANONYMOUS', 'ROLE_USER'].indexOf(this.props.role.name) &&
            <FormSection
              className="embedded-list-section"
              icon="fa fa-fw fa-id-badge"
              title={trans('groups')}
              disabled={this.props.new}
              actions={[
                {
                  name: 'add',
                  type: CALLBACK_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_group'),
                  callback: () => this.props.pickGroups(this.props.role.id)
                }
              ]}
            >
              <ListData
                name={selectors.STORE_NAME + '.roles.current.groups'}
                primaryAction={GroupList.open}
                fetch={{
                  url: ['apiv2_role_list_groups', {id: this.props.role.id}],
                  autoload: this.props.role.id && !this.props.new
                }}
                delete={{
                  url: ['apiv2_role_remove_groups', {id: this.props.role.id}]
                }}
                definition={GroupList.definition}
                card={GroupList.card}
              />
            </FormSection>
          }

          {this.props.workspace &&
            <FormSection
              className="embedded-list-section"
              icon="fa fa-fw fa-external-link"
              title={trans('shortcuts') + ' (' +
                (this.props.shortcuts && !!this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id) ?
                  this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id).data.length :
                  0) +
                '/' + workspaceConstants.SHORTCUTS_LIMIT + ')'
              }
              disabled={this.props.new}
              actions={[
                {
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_shortcut'),
                  disabled: !!this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id) &&
                    this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id).data.length >= workspaceConstants.SHORTCUTS_LIMIT,
                  modal: [MODAL_WORKSPACE_SHORTCUTS, {
                    workspace: this.props.workspace,
                    tools: Object.keys(this.props.role.tools || {}),
                    handleSelect: (selected) => this.props.addShortcuts(this.props.workspace.uuid, this.props.role.id, selected)
                  }]
                }
              ]}
            >
              <div className="list-group" fill={true}>
                {!!this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id) && 0 < this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id).data.length ?
                  this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id).data.map(shortcut =>
                    <ShortcutRow
                      key={`shortcut-${shortcut.type}-${shortcut.name}`}
                      name={shortcut.name}
                      type={shortcut.type}
                      label={this.getLabel(shortcut.type, shortcut.name)}
                      removeShortcut={() => this.props.removeShortcut(this.props.workspace.uuid, this.props.role.id, shortcut.type, shortcut.name)}
                    />
                  ) :
                  <div className="panel-body">
                    <em>{trans('no_shortcut')}</em>
                  </div>
                }
              </div>
            </FormSection>
          }
        </FormSections>
      </FormData>
    )
  }
}

RoleForm.propTypes = {
  path: T.string.isRequired,
  new: T.bool.isRequired,
  role: T.shape(RoleTypes.propTypes).isRequired,
  workspace: T.shape(WorkspaceType.propTypes),
  shortcuts: T.arrayOf(T.shape({
    role: T.shape(RoleTypes.propTypes),
    data: T.arrayOf(T.shape({
      type: T.string,
      name: T.string
    }))
  })),
  updateProp: T.func.isRequired,
  pickUsers: T.func.isRequired,
  pickGroups: T.func.isRequired,
  addShortcuts: T.func.isRequired,
  removeShortcut: T.func.isRequired
}

const Role = connect(
  state => ({
    new: formSelect.isNew(formSelect.form(state, selectors.STORE_NAME + '.roles.current')),
    role: formSelect.data(formSelect.form(state, selectors.STORE_NAME + '.roles.current')),
    path: toolSelectors.path(state),
    workspace: toolSelectors.contextData(state) ? toolSelectors.contextData(state) : null,
    shortcuts: toolSelectors.contextData(state) ? workspaceSelectors.shortcuts(state) : null
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
    pickGroups(roleId) {
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
    },
    addShortcuts(workspaceId, roleId, shortcuts) {
      dispatch(workspaceActions.addShortcuts(workspaceId, roleId, shortcuts))
    },
    removeShortcut(workspaceId, roleId, type, name) {
      dispatch(workspaceActions.removeShortcut(workspaceId, roleId, type, name))
    }
  })
)(RoleForm)

export {
  Role
}
