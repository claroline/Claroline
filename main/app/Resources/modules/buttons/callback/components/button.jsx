import React, {Component} from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {withModal} from '#/main/app/overlay/modal'
import {MODAL_CONFIRM} from '#/main/app/modals/confirm'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'

/**
 * Callback button.
 * Renders a component that will trigger a callback on click.
 */
class CallbackButtonComponent extends Component {
  constructor(props) {
    super(props)

    this.onClick = this.onClick.bind(this)
  }

  onClick(e) {
    if (!this.props.disabled) {
      if (this.props.confirm) {
        // show confirmation modal before executing
        this.props.showModal(MODAL_CONFIRM, {
          icon: this.props.confirm.icon,
          title: this.props.confirm.title,
          subtitle: this.props.confirm.subtitle,
          question: this.props.confirm.message,
          confirmButtonText: this.props.confirm.button,
          dangerous: this.props.dangerous,
          handleConfirm: () => {
            if (this.props.onClick) {
              // execute the default click callback if any (mostly to make dropdown works)
              this.props.onClick(e)
            }
            this.props.callback(e)
          }
        })
      } else {
        if (this.props.onClick) {
          // execute the default click callback if any (mostly to make dropdown works)
          this.props.onClick(e)
        }
        this.props.callback(e)
      }
    }

    e.preventDefault()
    e.stopPropagation()

    e.target.blur()
  }

  render() {
    return (
      <button
        {...omit(this.props, 'active', 'displayed', 'primary', 'dangerous', 'size', 'callback', 'bsRole', 'bsClass', 'confirm', 'showModal')}
        type="button"
        role="button"
        tabIndex={this.props.tabIndex}
        disabled={this.props.disabled}
        className={classes(
          this.props.className,
          this.props.size && `btn-${this.props.size}`,
          {
            disabled: this.props.disabled,
            default: !this.props.primary && !this.props.dangerous,
            primary: this.props.primary,
            danger: this.props.dangerous,
            active: this.props.active
          }
        )}
        onClick={this.onClick}
      >
        {this.props.children}
      </button>
    )
  }
}

implementPropTypes(CallbackButtonComponent, ButtonTypes, {
  showModal: T.func.isRequired, // comes from HOC withModal
  callback: T.func.isRequired
})

const CallbackButton = withModal(CallbackButtonComponent)

export {
  CallbackButton
}
