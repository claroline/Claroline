import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import get from 'lodash/get'
import isNumber from 'lodash/isNumber'

import {trans} from '#/main/app/intl/translation'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {LINK_BUTTON, MENU_BUTTON} from '#/main/app/buttons'

import {route} from '#/main/core/tool/routing'
import {Tool as ToolTypes} from '#/main/core/tool/prop-types'

const ToolsDropdown = (props) =>
  <div className="app-header-dropdown dropdown-menu dropdown-menu-right">
    <Toolbar
      className="list-group"
      buttonName="list-group-item"
      actions={props.tools
        .filter((tool) => !get(tool, 'restrictions.hidden', false))
        .map((tool) => ({
          name: tool.name,
          type: LINK_BUTTON,
          icon: `fa fa-fw fa-${tool.icon}`,
          label: trans(tool.name, {}, 'tools'),
          target: route(tool.name),
          order: get(tool, 'display.order')
        }))
        .sort((a, b) => {
          if (isNumber(a.order) && isNumber(b.order) && a.order !== b.order) {
            return a.order - b.order
          }

          if (a.label > b.label) {
            return 1
          }

          return -1
        })
      }
      onClick={props.closeMenu}
    />

    <div className="app-header-dropdown-footer">
      <Button
        className="btn-link btn-emphasis btn-block"
        type={LINK_BUTTON}
        label={trans('open-desktop', {}, 'actions')}
        target="/desktop"
        primary={true}
        onClick={props.closeMenu}
      />
    </div>
  </div>

ToolsDropdown.propTypes = {
  tools: T.arrayOf(T.shape(
    ToolTypes.propTypes
  )).isRequired,
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
    if (!this.props.isAuthenticated) {
      return null
    }

    return (
      <Button
        id="app-tools"
        className="app-header-item app-header-btn"
        type={MENU_BUTTON}
        icon={!this.props.loaded && this.state.opened ?
          'fa fa-fw fa-spinner fa-spin' :
          'fa fa-fw fa-atlas'
        }
        label={trans('desktop_tools')}
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
  isAuthenticated: T.bool.isRequired,
  loaded: T.bool.isRequired,
  tools: T.arrayOf(T.shape(
    ToolTypes.propTypes
  )).isRequired,
  getTools: T.func.isRequired
}

ToolsMenu.defaultProps = {
  tools: []
}

export {
  ToolsMenu
}
