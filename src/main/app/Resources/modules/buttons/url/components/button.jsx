import React, {forwardRef} from 'react'
import omit from 'lodash/omit'

import {url} from '#/main/app/api/router'
import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {buttonClasses} from '#/main/app/buttons/utils'

/**
 * URL button.
 * Renders a component that will navigate user to an url (internal or external) on click.
 *
 * IMPORTANT : if you need to navigate inside the current app, use `LinkButton` instead.
 */
const UrlButton = forwardRef((props, ref) => {
  let target = props.target
  if (Array.isArray(target)) {
    target = url(target)
  }

  return (
    <a
      {...omit(props, 'variant', 'active', 'displayed', 'primary', 'dangerous', 'size', 'target', 'confirm')}
      role="link"
      tabIndex={props.tabIndex}
      href={!props.disabled ? target : ''}
      ref={ref}
      disabled={props.disabled}
      className={buttonClasses(props.className, props.variant, props.size, props.disabled, props.active, props.primary, props.dangerous)}
      target={props.open}
    >
      {props.children}
    </a>
  )
})

// for debug purpose, otherwise component is named after the HOC
UrlButton.displayName = 'UrlButton'

implementPropTypes(UrlButton, ButtonTypes, {
  target: T.oneOfType([
    T.array, // a symfony url array
    T.string
  ]),
  open: T.string
})

export {
  UrlButton
}
