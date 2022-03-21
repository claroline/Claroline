import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON, LINK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {FormData} from '#/main/app/content/form/containers/data'
import {ListData} from '#/main/app/content/list/containers/data'
import {FormSections, FormSection} from '#/main/app/content/form/components/sections'

import {getActions as getWorkspaceActions} from '#/main/core/workspace/utils'
import {MODAL_WORKSPACE_SHORTCUTS} from '#/main/core/workspace/modals/shortcuts'
import {constants as workspaceConstants} from '#/main/core/workspace/constants'
import {selectors} from '#/main/core/tools/community/store'
import {Role as RoleTypes} from '#/main/core/user/prop-types'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'
import {GroupList} from '#/main/core/administration/community/group/components/group-list'
import {UserList} from '#/main/core/user/components/list'
import {MODAL_USERS} from '#/main/core/modals/users'
import {MODAL_GROUPS} from '#/main/core/modals/groups'

import {RoleTools} from '#/main/core/tools/community/role/components/tools'
import {MODAL_ROLE_RIGHTS} from '#/main/core/tools/community/role/modals/rights'

// TODO : merge with main/core/administration/community/role/components/role

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

class Role extends Component {
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
        target={(role, isNew) => isNew ? ['apiv2_role_create'] : ['apiv2_role_update', {id: role.id}]}
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
              }, {
                name: 'name',
                type: 'string',
                label: trans('code'),
                displayed: !this.props.new,
                disabled: true,
                required: true
              }
            ]
          }
        ]}
      >
        <FormSections level={3}>
          <FormSection
            icon="fa fa-fw fa-cogs"
            title={trans('tools')}
            disabled={this.props.new}
            actions={[
              {
                name: 'set-rights',
                type: MODAL_BUTTON,
                icon: 'fa fa-fw fa-pencil',
                label: trans('edit', {}, 'actions'),
                modal: [MODAL_ROLE_RIGHTS, {
                  role: this.props.role,
                  rights: this.props.role.tools,
                  workspace: this.props.workspace,
                  onSave: () => this.props.reload(this.props.role.id, this.props.workspace)
                }]
              }
            ]}
          >
            <RoleTools
              fill={true}
              tools={this.props.role.tools}
            />
          </FormSection>

          {-1 === ['ROLE_ANONYMOUS', 'ROLE_USER'].indexOf(this.props.role.name) &&
            <FormSection
              className="embedded-list-section"
              icon="fa fa-fw fa-user"
              title={trans('users')}
              disabled={!this.props.role.id  || this.props.new}
              actions={[
                {
                  name: 'add-users',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_user'),
                  modal: [MODAL_USERS, {
                    selectAction: (selected) => ({
                      type: CALLBACK_BUTTON,
                      label: trans('add', {}, 'actions'),
                      callback: () => this.props.addUsers(this.props.role.id, selected)
                    })
                  }]
                }
              ]}
            >
              {this.props.role.id && !this.props.new &&
                <UserList
                  name={selectors.STORE_NAME + '.roles.current.users'}
                  url={['apiv2_role_list_users', {id: this.props.role.id}]}
                  delete={{
                    url: ['apiv2_role_remove_users', {id: this.props.role.id}]
                  }}
                />
              }
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
                  name: 'add-groups',
                  type: MODAL_BUTTON,
                  icon: 'fa fa-fw fa-plus',
                  label: trans('add_group'),
                  modal: [MODAL_GROUPS, {
                    selectAction: (selected) => ({
                      type: CALLBACK_BUTTON,
                      label: trans('add', {}, 'actions'),
                      callback: () => this.props.addGroups(this.props.role.id, selected)
                    })
                  }]
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
                    handleSelect: (selected) => this.props.addShortcuts(this.props.workspace.id, this.props.role.id, selected)
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
                      removeShortcut={() => this.props.removeShortcut(this.props.workspace.id, this.props.role.id, shortcut.type, shortcut.name)}
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

Role.propTypes = {
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
  reload: T.func.isRequired,
  addUsers: T.func.isRequired,
  addGroups: T.func.isRequired,
  addShortcuts: T.func.isRequired,
  removeShortcut: T.func.isRequired
}

export {
  Role
}
