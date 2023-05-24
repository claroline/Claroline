import React, {forwardRef} from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {
  NavLink,
  matchPath,
  useLocation
} from '#/main/app/router'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'

// todo implement confirm behavior

/**
 * Link button.
 * Renders a component that will navigate user in the current app on click.
 */
const LinkButton = forwardRef((props, ref) => {
  const location = useLocation()

  return (
    <NavLink
      {...omit(props, 'displayed', 'primary', 'dangerous', 'size', 'target', 'confirm', 'history', 'match', 'staticContext', 'active')}
      ref={ref}
      tabIndex={props.tabIndex}
      to={props.target}
      exact={props.exact}
      disabled={props.disabled || matchPath(location.pathname, {path: props.target, exact: true})}
      className={classes(
        props.className,
        props.size && `btn-${props.size}`,
        {
          disabled: props.disabled,
          default: !props.primary && !props.dangerous,
          primary: props.primary,
          danger: props.dangerous,
          active: props.active
        }
      )}
    >
      {props.children}
    </NavLink>
  )
})

implementPropTypes(LinkButton, ButtonTypes, {
  target: T.string,
  exact: T.bool
}, {
  exact: false
})

//const LinkButton = withRouter(LinkButtonComponent)

export {
  LinkButton
}
