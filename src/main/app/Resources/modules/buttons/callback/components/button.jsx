import React, {forwardRef, useCallback} from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {buttonClasses} from '#/main/app/buttons/utils'

/**
 * Callback button.
 * Renders a component that will trigger a callback on click.
 */
const CallbackButton = forwardRef((props, ref) => {
  const onClick = useCallback((e) => {
    if (!props.disabled) {
      if (props.onClick) {
        // execute the default click callback if any (mostly to make dropdown works)
        props.onClick(e)
      }
      props.callback(e)
    }

    e.preventDefault()
    e.stopPropagation()

    e.target.blur()
  }, [props.disabled, props.callback, props.onClick])

  return (<button
    {...omit(props, 'variant', 'active', 'displayed', 'primary', 'dangerous', 'size', 'callback', 'htmlType')}
    ref={ref}
    type={props.htmlType}
    role="button"
    tabIndex={props.tabIndex}
    disabled={props.disabled}
    className={buttonClasses(props.className, props.variant, props.size, props.disabled, props.active, props.primary, props.dangerous)}
    onClick={onClick}
  >
    {props.children}
  </button>)
})

// for debug purpose, otherwise component is named after the HOC
CallbackButton.displayName = 'CallbackButton'

implementPropTypes(CallbackButton, ButtonTypes, {
  callback: T.func.isRequired,
  htmlType: T.oneOf(['button', 'submit'])
}, {
  htmlType: 'button'
})

export {
  CallbackButton
}
