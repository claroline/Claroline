import React, {forwardRef} from 'react'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {
  NavLink
} from '#/main/app/router'

import {Button as ButtonTypes} from '#/main/app/buttons/prop-types'
import {buttonClasses} from '#/main/app/buttons/utils'
import {scrollTo} from '#/main/app/dom/scroll'

/**
 * Link button.
 * Renders a component that will navigate user in the current app on click.
 */
const LinkButton = forwardRef((props, ref) =>
  <NavLink
    {...omit(props, 'variant', 'active', 'displayed', 'primary', 'dangerous', 'size', 'target', 'confirm', 'history', 'match', 'staticContext')}
    ref={ref}
    tabIndex={props.tabIndex}
    to={props.target}
    exact={props.exact}
    disabled={props.disabled}
    className={buttonClasses(props.className, props.variant, props.size, props.disabled, props.active, props.primary, props.dangerous)}
    onClick={(e) => {
      console.log('coucou')
      scrollTo('.app-page')

      if (props.onClick) {
        props.onClick(e)
      }
    }}
  >
    {props.children}
  </NavLink>
)

// for debug purpose, otherwise component is named after the HOC
LinkButton.displayName = 'LinkButton'

implementPropTypes(LinkButton, ButtonTypes, {
  target: T.string,
  exact: T.bool,
  autoScroll: T.bool
}, {
  exact: false
})

export {
  LinkButton
}
