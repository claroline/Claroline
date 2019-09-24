import React, {Component} from 'react'
import classes from 'classnames'
import merge from 'lodash/merge'
import omit from 'lodash/omit'

import {trans} from '#/main/app/intl/translation'
import {toKey} from '#/main/core/scaffolding/text'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {CALLBACK_BUTTON} from '#/main/app/buttons'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

class SummaryLink extends Component {
  constructor(props) {
    super(props)

    this.state = {
      collapsed: this.props.collapsed || false
    }

    this.toggleCollapse = this.toggleCollapse.bind(this)
  }

  componentDidUpdate(prevProps) {
    if (prevProps.collapsed !== this.props.collapsed) {
      this.setState({collapsed: this.props.collapsed || false})
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
        <div className={classes('summary-link', {
          active: this.props.active
        })}>
          <Button
            className="btn-summary-primary"
            {...omit(this.props, 'children', 'additional', 'collapsible', 'collapsed', 'toggleCollapse')}
          />

          {(collapsible || 0 !== this.props.additional.length) &&
            <Toolbar
              className="summary-link-actions"
              buttonName="btn-summary"
              tooltip="bottom"
              toolbar="collapse more"
              actions={(this.props.additional || []).concat([
                {
                  type: CALLBACK_BUTTON,
                  name: 'collapse',
                  icon: classes('fa fa-fw', {
                    'fa-caret-right': this.state.collapsed,
                    'fa-caret-down': !this.state.collapsed
                  }),
                  displayed: collapsible,
                  label: trans(this.state.collapsed ? 'expand': 'collapse', {}, 'actions'),
                  callback: this.toggleCollapse
                }
              ])}
            />
          }
        </div>

        {!this.state.collapsed && this.props.children.length > 0 &&
          <ul className="step-children">
            {this.props.children.map((child, index) =>
              <SummaryLink
                {...child}
                key={toKey(child.label) + index}
              />
            )}
          </ul>
        }
      </li>
    )
  }
}

implementPropTypes(SummaryLink, ActionTypes, {
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

const Summary = props => {
  if (0 !== props.links.length ) {
    return (
      <ul className="summary">
        {props.links.map((link, index) =>
          <SummaryLink
            {...link}
            key={toKey(link.label) + index}
          />
        )}
      </ul>
    )
  }

  return null
}

Summary.propTypes = {
  links: T.arrayOf(T.shape(merge({}, ActionTypes.propTypes, {
    collapsed: T.bool,
    // It forces the display of the collapse button even if children is empty
    // It permits to dynamic load the children
    collapsible: T.bool,
    toggleCollapse: T.func,
    additional: T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // TODO : find a way to document more nesting
    children: T.arrayOf(T.shape(
      ActionTypes.propTypes
    ))
  })))
}

export {
  Summary
}
