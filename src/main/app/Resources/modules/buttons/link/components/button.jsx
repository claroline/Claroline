import React, {forwardRef} from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {
  NavLink,
  matchPath,
  useLocation
} from '#/main/app/router'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {buttonClasses} from '#/main/app/buttons/utils'

/**
 * Link button.
 * Renders a component that will navigate user in the current app on click.
 */
const LinkButton = forwardRef((props, ref) => {
  const location = useLocation()

  return (
    <NavLink
      {...omit(props, 'variant', 'active', 'displayed', 'primary', 'dangerous', 'size', 'target', 'confirm', 'history', 'match', 'staticContext')}
      ref={ref}
      tabIndex={props.tabIndex}
      to={props.target}
      exact={props.exact}
      disabled={props.disabled || matchPath(location.pathname, {path: props.target, exact: true})}
      className={buttonClasses(props.className, props.variant, props.size, props.disabled, props.active, props.primary, props.dangerous)}
    >
      {props.children}
    </NavLink>
  )
})

// for debug purpose, otherwise component is named after the HOC
LinkButton.displayName = 'LinkButton'

implementPropTypes(LinkButton, ButtonTypes, {
  target: T.string,
  exact: T.bool
}, {
  exact: false
})

export {
  LinkButton
}
