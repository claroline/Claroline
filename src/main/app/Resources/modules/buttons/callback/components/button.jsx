import React, {Component} from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'

/**
 * Callback button.
 * Renders a component that will trigger a callback on click.
 */
class CallbackButton extends Component {
  constructor(props) {
    super(props)

    this.onClick = this.onClick.bind(this)
  }

  onClick(e) {
    if (!this.props.disabled) {
      if (this.props.onClick) {
        // execute the default click callback if any (mostly to make dropdown works)
        this.props.onClick(e)
      }
      this.props.callback(e)
    }

    e.preventDefault()
    e.stopPropagation()

    e.target.blur()
  }

  render() {
    return (
      <button
        {...omit(this.props, 'active', 'displayed', 'primary', 'dangerous', 'size', 'callback', 'bsRole', 'bsClass', 'htmlType')}
        type={this.props.htmlType}
        role="button"
        tabIndex={this.props.tabIndex}
        disabled={this.props.disabled}
        className={classes(this.props.className, {
          [`btn-${this.props.size}`]: !!this.props.size,
          disabled: this.props.disabled,
          default: !this.props.primary && !this.props.dangerous,
          primary: this.props.primary,
          danger: this.props.dangerous,
          active: this.props.active
        })}
        onClick={this.onClick}
      >
        {this.props.children}
      </button>
    )
  }
}

implementPropTypes(CallbackButton, ButtonTypes, {
  callback: T.func.isRequired,
  htmlType: T.oneOf(['button', 'submit'])
}, {
  htmlType: 'button'
})

export {
  CallbackButton
}
