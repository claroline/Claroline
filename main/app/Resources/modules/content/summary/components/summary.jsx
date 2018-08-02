import React, {Component} from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {trans} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Button} from '#/main/app/action/components/button'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {Summary as SummaryTypes} from '#/main/app/content/summary/prop-types'

const SummaryHeader = props =>
  <header className="summary-header">
    <h3 className="summary-title">
      <span className="fa fa-fw fa-ellipsis-v icon-with-text-right" />

      {props.title}
    </h3>

    <div className="summary-controls">
      {props.opened &&
        <Button
          type={CALLBACK_BUTTON}
          tooltip={props.opened ? 'bottom':'right'}
          className={classes('btn-link summary-control hidden-xs hidden-sm', {
            active: props.pinned
          })}
          icon="fa fa-fw fa-map-pin"
          label={trans(props.pinned ? 'unpin_summary':'pin_summary', {}, 'path')}
          callback={props.togglePin}
        />
      }

      <Button
        type="callback"
        tooltip={props.opened ? 'bottom':'right'}
        className="btn-link summary-control"
        icon={classes('fa fa-fw', {
          'fa-chevron-left': props.opened,
          'fa-chevron-right': !props.opened
        })}
        label={trans(props.opened ? 'close_summary':'open_summary', {}, 'path')}
        callback={props.toggleOpen}
      />
    </div>
  </header>

SummaryHeader.propTypes = {
  title: T.string,
  opened: T.bool,
  pinned: T.bool,
  togglePin: T.func.isRequired,
  toggleOpen: T.func.isRequired
}

class SummaryLink extends Component {
  constructor(props) {
    super(props)

    this.state = {
      collapsed: this.props.collapsed || false
    }
  }

  componentWillReceiveProps(nextProps) {
    if (nextProps.collapsed !== this.props.collapsed) {
      this.setState({collapsed: nextProps.collapsed || false})
    }
  }

  toggleCollapse() {
    if (this.props.toggleCollapse) {
      this.props.toggleCollapse(!this.state.collapsed)
    }

    this.setState({collapsed: !this.state.collapsed})
  }

  render() {
    const collapsible = this.props.collapsible || (this.props.children && 0 !== this.props.children.length)

    return (
      <li className="summary-link-container">
        <div className="summary-link">
          <Button
            {...omit(this.props, 'opened', 'children', 'additional', 'collapsible', 'collapsed', 'toggleCollapse')}
            tooltip={!this.props.opened ? 'right' : undefined}
          />

          {(this.props.opened && (collapsible || 0 !== this.props.additional.length)) &&
            <div className="step-actions">
              {this.props.additional
                .filter(action => undefined === action.displayed || action.displayed)
                .map((action, index) =>
                  <Button
                    {...action}
                    key={toKey(action.label) + index}
                    tooltip="bottom"
                    className="btn-link btn-summary"
                  />
                )
              }

              {collapsible &&
                <Button
                  type="callback"
                  tooltip="bottom"
                  className="btn-link btn-summary"
                  icon={classes('fa', {
                    'fa-caret-right': this.state.collapsed,
                    'fa-caret-down': !this.state.collapsed
                  })}
                  label={trans(this.state.collapsed ? 'expand': 'collapse', {}, 'actions')}
                  callback={this.toggleCollapse.bind(this)}
                />
              }
            </div>
          }
        </div>

        {!this.state.collapsed && this.props.children.length > 0 &&
          <ul className="step-children">
            {this.props.children.map((child, index) =>
              <SummaryLink
                {...child}
                key={toKey(child.label) + index}
                opened={this.props.opened}
              />
            )}
          </ul>
        }
      </li>
    )
  }
}

implementPropTypes(SummaryLink, ActionTypes, {
  opened: T.bool.isRequired,
  additional: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  children: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  toggleCollapse: T.func,
  collapsed: T.bool,
  // It forces the display of the collapse button even if children is empty
  // It permits to dynamic load the children
  collapsible: T.bool
}, {
  additional: [],
  children: [],
  collapsed: false,
  collapsible: false
})

class Summary extends Component {
  constructor(props) {
    super(props)

    this.state = {
      opened: props.opened,
      pinned: props.pinned
    }
  }

  togglePin() {
    this.setState({pinned: !this.state.pinned})
  }

  toggleOpen() {
    this.setState({opened: !this.state.opened})
  }

  render() {
    return (
      <aside className={classes('summary-container', {
        opened: this.state.opened,
        pinned: this.state.pinned
      })}>
        <SummaryHeader
          title={this.props.title}
          opened={this.state.opened}
          pinned={this.state.pinned}
          togglePin={this.togglePin.bind(this)}
          toggleOpen={this.toggleOpen.bind(this)}
        />

        {0 !== this.props.links.length &&
          <ul className="summary">
            {this.props.links.map((link, index) =>
              <SummaryLink
                {...link}
                key={toKey(link.label) + index}
                opened={this.state.opened}
              />
            )}
          </ul>
        }
      </aside>
    )
  }
}

implementPropTypes(Summary, SummaryTypes)

export {
  Summary
}
