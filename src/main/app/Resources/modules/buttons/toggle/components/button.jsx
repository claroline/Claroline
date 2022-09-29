import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'

const ToggleButton = props =>
  <button
    {...omit(props, 'active', 'displayed', 'primary', 'dangerous', 'size', 'target', 'confirm', 'toggle', 'enabled')}
    role="button"
    tabIndex={props.tabIndex}
    disabled={props.disabled}
    className={classes('btn-toggle', props.className, {
      disabled: props.disabled,
      default: !props.primary && !props.dangerous,
      primary: props.primary,
      danger: props.dangerous,
      active: props.active
    }, props.size && `btn-${props.size}`)}
    aria-pressed={props.enabled}
    onClick={(e) => {
      if (!props.disabled) {
        if (props.onClick) {
          // execute the default click callback if any
          props.onClick(e)
        }

        if (props.toggle) {
          props.toggle(!props.enabled)
        }
      }

      e.preventDefault()
      e.stopPropagation()

      e.target.blur()
    }}
  >
    <span
      className={classes('fa fa-fw', {
        'fa-check': props.enabled,
        'fa-times': !props.enabled
      })}
      aria-hidden={true}
    />

    {props.children}
  </button>

implementPropTypes(ToggleButton, ButtonTypes, {
  enabled: T.bool,
  toggle: T.func
}, {
  enabled: false
})

export {
  ToggleButton
}
