import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/core/scaffolding/prop-types'
import {NavLink} from '#/main/core/router'
import {Button as ButtonTypes} from '#/main/app/button/prop-types'

// todo implement confirm behavior

/**
 * Link button.
 * Renders a component that will navigate user in the current app on click.
 *
 * @param props
 * @constructor
 */
const LinkButton = props =>
  <NavLink
    {...omit(props, 'displayed', 'primary', 'dangerous', 'size', 'target', 'confirm')}
    tabIndex={props.tabIndex}
    to={props.target}
    exact={props.exact}
    disabled={props.disabled}
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

implementPropTypes(LinkButton, ButtonTypes, {
  target: T.string,
  exact: T.bool
}, {
  exact: false
})

export {
  LinkButton
}
