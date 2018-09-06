/* global window */

import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import times from 'lodash/times'

// TODO : remove us when toolbar bars will be mounted in the main app
import {ModalOverlay} from '#/main/app/overlay/modal/containers/overlay'
import {AlertOverlay} from '#/main/app/overlay/alert/containers/overlay'

import {trans} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'
import {Button} from '#/main/app/action/components/button'
import {MENU_BUTTON, URL_BUTTON} from '#/main/app/buttons'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

// todo : force the display of active tool when collapsed

const ToolLink = props =>
  <Button
    className="tool-link"
    type={URL_BUTTON}
    icon={`fa fa-fw fa-${props.icon}`}
    label={trans(props.name, {}, 'tools')}
    tooltip="right"
    target={props.target}
    active={props.active}
  />

ToolLink.propTypes = {
  icon: T.string.isRequired,
  name: T.string.isRequired,
  target: T.array.isRequired,
  active: T.bool
}

const MoreTools = props =>
  <Button
    id="toolbar-more-tools"
    className="tool-link"
    type={MENU_BUTTON}
    icon="fa fa-wrench"
    label={trans('show-more-tools', {}, 'actions')}
    tooltip="right"
    menu={{
      label: trans('tools'),
      items: props.tools.map(tool => ({
        type: URL_BUTTON,
        icon: `fa fa-fw fa-${tool.icon}`,
        label: trans(tool.name, {}, 'tools'),
        target: tool.open
      }))
    }}
    subscript={{
      type: 'label',
      status: 'primary',
      value: props.tools.length
    }}
  />

MoreTools.propTypes = {
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    open: T.oneOfType([T.array, T.string])
  })).isRequired
}

const MoreActions = props =>
  <Button
    id="toolbar-more-actions"
    className="tool-link"
    type="menu"
    icon="fa fa-fw fa-ellipsis-v"
    label={trans('show-more-actions', {}, 'actions')}
    tooltip="right"
    menu={{
      label: trans('actions'),
      items: props.actions
    }}
  />

MoreActions.propTypes = {
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )).isRequired
}

class Toolbar extends Component {
  constructor(props) {
    super(props)

    this.toolbarContainer = null

    const actions = this.props.actions.filter(action => undefined === action.displayed || action.displayed)
    this.state = {
      displayedTools: this.props.tools.length,
      displayedActions: actions.length
    }

    this.resize = this.resize.bind(this)
  }

  componentDidMount() {
    this.resize()

    window.addEventListener('resize', this.resize)
  }

  componentWillUnmount() {
    window.removeEventListener('resize', this.resize)
  }

  resize() {
    // get available height
    const height = this.toolbarContainer.offsetHeight

    const actions = this.props.actions.filter(action => undefined === action.displayed || action.displayed)

    // todo : calculate it
    const linkHeight = 52
    const additionalSpacing = 10 + 30 // navbar v-padding + tools v-margin

    const availableHeight = height - additionalSpacing

    // calculate the number of link we will can display at once
    let displayedTools = this.props.tools.length
    let displayedActions = actions.length

    // calculate the full height needed to render the current toolbar
    let fullHeight = additionalSpacing + (linkHeight * displayedTools) + (linkHeight * displayedActions)
    // check if there is enough space to display the full toolbar
    if (fullHeight > availableHeight) {
      // we need to collapse some things

      // start by collapsing all actions
      displayedActions = 0

      // get remaining height for tools
      // we keep one space in order to add the 'MoreButton' for actions if needed
      let toolsAvailableHeight = availableHeight - (linkHeight * (actions.length ? 1 : 0))
      if (this.props.primary) {
        toolsAvailableHeight -= linkHeight
      }

      // check if there is enough space to display all tools
      if (linkHeight * displayedTools > toolsAvailableHeight) {
        // we need to collapse tools
        // we remove one tool space in order to have space for the 'MoreButton'
        displayedTools = Math.trunc((toolsAvailableHeight - linkHeight) / linkHeight)
      }
    }

    this.setState({
      displayedTools: displayedTools,
      displayedActions: displayedActions
    })
  }

  render() {
    const displayedActions = this.props.actions.filter(action => undefined === action.displayed || action.displayed)

    return (
      <nav ref={(element) => this.toolbarContainer = element}>
        {this.props.primary &&
          <ToolLink
            icon={this.props.primary.icon}
            name={this.props.primary.name}
            target={this.props.primary.open}
            active={this.props.active === this.props.primary.name}
          />
        }

        {0 !== this.props.tools.length &&
          <nav className="tools">
            {times(this.state.displayedTools, (i) =>
              <ToolLink
                {...this.props.tools[i]}

                key={this.props.tools[i].name}
                target={this.props.tools[i].open}
                active={this.props.active === this.props.tools[i].name}
              />
            )}
          </nav>
        }

        {(0 !== displayedActions.length || this.state.displayedTools !== this.props.tools.length) &&
          <nav className="additional-tools">
            {this.state.displayedTools !== this.props.tools.length &&
              <MoreTools
                tools={this.props.tools.slice(this.state.displayedTools)}
              />
            }

            {times(this.state.displayedActions, (i) =>
              <Button
                {...displayedActions[i]}
                key={toKey(displayedActions[i].label)}
                className="tool-link"
                tooltip="right"
              />
            )}

            {this.state.displayedActions !== displayedActions.length &&
              <MoreActions
                actions={displayedActions.slice(this.state.displayedActions)}
              />
            }
          </nav>
        }

        <AlertOverlay />
        <ModalOverlay />
      </nav>
    )
  }
}

Toolbar.propTypes = {
  active: T.string,
  primary: T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    open: T.oneOfType([T.array, T.string])
  }),
  tools: T.arrayOf(T.shape({
    icon: T.string.isRequired,
    name: T.string.isRequired,
    open: T.oneOfType([T.array, T.string])
  })),
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
}

Toolbar.defaultProps = {
  tools: [],
  actions: []
}

export {
  Toolbar
}
