import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {withModal, MODAL_CONFIRM} from '#/main/core/layout/modal'
import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {Button as ButtonTypes} from '#/main/app/button/prop-types'

/**
 * Callback button.
 * Renders a component that will trigger a callback on click.
 *
 * @param props
 * @constructor
 */
const CallbackButton = withModal(props =>
  <button
    {...omit(props, 'displayed', 'primary', 'dangerous', 'size', 'callback', 'bsRole', 'bsClass', 'confirm', 'showModal')}
    type="button"
    role="button"
    tabIndex={props.tabIndex}
    disabled={props.disabled}
    className={classes(
      props.className,
      props.size && `btn-${props.size}`,
      {
        disabled: props.disabled,
        default: !props.primary && !props.dangerous,
        primary: props.primary,
        dangerous: props.dangerous,
        active: props.active
      }
    )}
    onClick={(e) => {
      if (!props.disabled) {
        if (props.confirm) {
          // show confirmation modal before executing
          props.showModal(MODAL_CONFIRM, {
            icon: props.confirm.icon,
            title: props.confirm.title,
            question: props.confirm.message,
            confirmButtonText: props.confirm.button,
            dangerous: props.dangerous,
            handleConfirm: () => {
              props.callback(e)

              if (props.onClick) {
                // execute the default click callback if any (mostly to make dropdown works)
                props.onClick(e)
              }
            }
          })
        } else {
          props.callback(e)

          if (props.onClick) {
            // execute the default click callback if any (mostly to make dropdown works)
            props.onClick(e)
          }
        }
      }

      e.preventDefault()
      e.stopPropagation()

      e.target.blur()
    }}
  >
    {props.children}
  </button>
)

implementPropTypes(CallbackButton, ButtonTypes, {
  //showModal: T.func.isRequired, // injected from `withModal`
  callback: T.func.isRequired
})

export {
  CallbackButton
}
