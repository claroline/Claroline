import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/tool/routing'

const ToolsDropdown = props =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right">
    {props.tools.map(tool =>
      <Button
        key={tool.name}
        type={LINK_BUTTON}
        icon={`fa fa-fw fa-${tool.icon}`}
        label={trans(tool.name, {}, 'tools')}
        target={route(tool.name)}
      />
    )}
  </div>

ToolsDropdown.propTypes = {
  tools: T.arrayOf(T.shape({
    // TODO : prop-types
  })).isRequired,
  closeMenu: T.func.isRequired
}

class ToolsMenu extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: false
    }

    this.setOpened = this.setOpened.bind(this)
  }

  setOpened(opened) {
    this.setState({opened: opened})
  }

  render() {
    return (
      <Button
        id="app-tools"
        className="app-header-item app-header-btn"
        type={MENU_BUTTON}
        icon={!this.props.loaded && this.state.opened ?
          'fa fa-fw fa-spinner fa-spin' :
          'fa fa-fw fa-th'
        }
        label={trans('tools')}
        tooltip="bottom"
        opened={this.props.loaded && this.state.opened}
        onToggle={(opened) => {
          if (opened) {
            this.props.getTools()
          }

          this.setOpened(opened)
        }}
        menu={
          <ToolsDropdown
            tools={this.props.tools}
            closeMenu={() => this.setOpened(false)}
          />
        }
      />
    )
  }
}

ToolsMenu.propTypes = {
  loaded: T.bool.isRequired,
  tools: T.arrayOf(T.shape({
    // TODO : prop-types
  })).isRequired,
  getTools: T.func.isRequired
}

ToolsMenu.defaultProps = {
  tools: []
}

export {
  ToolsMenu
}
