import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl'
import {hasPermission} from '#/main/app/security'
import {Button} from '#/main/app/action'
import {CALLBACK_BUTTON, MODAL_BUTTON} from '#/main/app/buttons'
import {ContentSection} from '#/main/app/content/components/sections'

import {constants as workspaceConstants} from '#/main/core/workspace/constants'
import {getActions as getWorkspaceActions} from '#/main/core/workspace/utils'
import {MODAL_WORKSPACE_SHORTCUTS} from '#/main/core/workspace/modals/shortcuts'

import {Role as RoleTypes} from '#/main/community/role/prop-types'

const ShortcutRow = props =>
  <div className="tool-rights-row list-group-item">
    <div className="tool-rights-title">
      {props.label}
    </div>
    <div className="tool-rights-actions">
      <Button
        className="btn btn-link"
        type={CALLBACK_BUTTON}
        icon="fa fa-fw fa-trash"
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

class RoleShortcuts extends Component {
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
    let shortcuts = []
    if (this.props.shortcuts) {
      const roleShortcuts = this.props.shortcuts.find(shortcut => shortcut.role.id === this.props.role.id)
      if (roleShortcuts) {
        shortcuts = roleShortcuts.data
      }
    }

    return (
      <ContentSection
        {...omit(this.props, 'workspace', 'role', 'tools', 'shortcuts', 'addShortcuts', 'removeShortcuts')}
        id={this.props.id}
        icon="fa fa-fw fa-external-link"
        title={trans('shortcuts') + ' (' + shortcuts.length  + '/' + workspaceConstants.SHORTCUTS_LIMIT + ')'}
        disabled={this.props.disabled}
        actions={[
          {
            type: MODAL_BUTTON,
            icon: 'fa fa-fw fa-plus',
            label: trans('add_shortcut'),
            disabled: shortcuts.length >= workspaceConstants.SHORTCUTS_LIMIT,
            displayed: hasPermission('edit', this.props.role),
            modal: [MODAL_WORKSPACE_SHORTCUTS, {
              workspace: this.props.workspace,
              tools: this.props.tools.map(tool => tool.name),
              handleSelect: (selected) => this.props.addShortcuts(this.props.workspace.id, this.props.role.id, selected)
            }]
          }
        ]}
      >
        {0 === shortcuts.length &&
          <em>{trans('no_shortcut')}</em>
        }

        {0 !== shortcuts.length &&
          <div className="list-group" fill={true}>
            {shortcuts.map(shortcut =>
              <ShortcutRow
                key={`shortcut-${shortcut.type}-${shortcut.name}`}
                name={shortcut.name}
                type={shortcut.type}
                label={this.getLabel(shortcut.type, shortcut.name)}
                removeShortcut={() => this.props.removeShortcut(this.props.workspace.id, this.props.role.id, shortcut.type, shortcut.name)}
              />
            )}
          </div>
        }
      </ContentSection>
    )
  }
}

RoleShortcuts.propTypes = {
  id: T.string,
  disabled: T.bool,
  workspace: T.object,
  role: T.shape(RoleTypes.propTypes),
  tools: T.array,
  shortcuts: T.arrayOf(T.shape({
    role: T.shape(RoleTypes.propTypes),
    data: T.arrayOf(T.shape({
      type: T.string,
      name: T.string
    }))
  })),
  addShortcuts: T.func.isRequired,
  removeShortcut: T.func.isRequired
}

export {
  RoleShortcuts
}
