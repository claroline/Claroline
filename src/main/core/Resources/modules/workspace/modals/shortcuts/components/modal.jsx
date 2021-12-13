import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import omit from 'lodash/omit'
import cloneDeep from 'lodash/cloneDeep'

import {trans} from '#/main/app/intl/translation'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Button} from '#/main/app/action/components/button'
import {Modal} from '#/main/app/overlays/modal/components/modal'
import {Checkbox} from '#/main/app/input/components/checkbox'

import {getActions} from '#/main/core/workspace/utils'
import {Workspace as WorkspaceType} from '#/main/core/workspace/prop-types'

const ShortcutRow = props =>
  <div className="tool-rights-row list-group-item">
    <div className="tool-rights-title">
      {props.label}
    </div>
    <div className="tool-rights-actions">
      <Checkbox
        key={`${props.type}-${props.name}-chk`}
        id={`${props.type}-${props.name}-chk`}
        checked={props.checked}
        onChange={checked => props.updateChecked(checked)}
      />
    </div>
  </div>

ShortcutRow.propTypes = {
  name: T.string.isRequired,
  label: T.string.isRequired,
  type: T.string.isRequired,
  checked: T.bool.isRequired,
  updateChecked: T.func.isRequired
}

class ShortcutsModal extends Component {
  constructor(props) {
    super(props)

    this.state = {
      actions: [],
      selected: []
    }
  }

  componentDidMount() {
    if (this.props.workspace) {
      getActions([this.props.workspace], {}, '', null).then((actions) => this.setState({actions: actions}))
    }
  }

  handleSelection(checked, type, name) {
    const index = this.state.selected.findIndex(s => type === s.type && name === s.name)
    const newSelected = cloneDeep(this.state.selected)

    if (checked && -1 === index) {
      newSelected.push({
        type: type,
        name: name
      })
    } else if (!checked && -1 < index) {
      newSelected.splice(index, 1)
    }
    this.setState({selected: newSelected})
  }

  getActionLabel(name) {
    const action = this.state.actions.find(a => a.name === name)

    return action ? action.label : name
  }

  render() {
    return (
      <Modal
        {...omit(this.props, 'tools', 'handleSelect')}
        title={trans('add_shortcut')}
        icon="fa fa-fw fa-external-link"
      >
        {this.props.tools.map(toolName =>
          <ShortcutRow
            key={`shortcut-tool-${toolName}`}
            name={toolName}
            type="tool"
            label={trans(toolName, {}, 'tools')}
            checked={-1 < this.state.selected.findIndex(s => 'tool' === s.type && toolName === s.name)}
            updateChecked={checked => this.handleSelection(checked, 'tool', toolName)}
          />
        )}

        {this.state.actions.map(action =>
          <ShortcutRow
            key={`shortcut-action-${action.name}`}
            name={action.name}
            type="action"
            label={this.getActionLabel(action.name)}
            checked={-1 < this.state.selected.findIndex(s => 'action' === s.type && action.name === s.name)}
            updateChecked={checked => this.handleSelection(checked, 'action', action.name)}
          />
        )}

        <Button
          type={CALLBACK_BUTTON}
          label={trans('add', {}, 'actions')}
          className="modal-btn btn"
          primary={true}
          disabled={0 === this.state.selected.length}
          callback={() => {
            this.props.handleSelect(this.state.selected)
            this.props.fadeModal()
          }}
        />
      </Modal>
    )
  }
}

ShortcutsModal.propTypes = {
  workspace: T.shape(WorkspaceType.propTypes),
  tools: T.array.isRequired,
  handleSelect: T.func.isRequired,
  fadeModal: T.func.isRequired
}

ShortcutsModal.defaultProps = {
  tools: []
}

export {
  ShortcutsModal
}
