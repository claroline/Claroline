import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

/**
 * Link element.
 *
 * @param props
 * @constructor
 */
const Link = props =>
  <a
    href={!props.disabled ? props.target : ''}
    disabled={props.disabled}
    className={classes(
      'btn',
      props.className,
      {disabled: props.disabled}
    )}
  >
    {props.children}
  </a>

Link.propTypes = {
  children: T.node.isRequired,
  disabled: T.bool,
  target: T.string,
  className: T.string
}

Link.defaultProps = {
  disabled: false
}

/**
 * Button element.
 *
 * @param props
 * @constructor
 */
const Button = props =>
  <button
    type="button"
    disabled={props.disabled}
    className={classes(
      'btn',
      props.className,
      {disabled: props.disabled}
    )}
    onClick={() => !props.disabled && props.onClick()}
  >
    {props.children}
  </button>

Button.propTypes = {
  children: T.node.isRequired,
  disabled: T.bool,
  onClick: T.func,
  className: T.string
}

Button.defaultProps = {
  position: 'top',
  disabled: false
}

/**
 * Link with tooltip.
 *
 * @param props
 * @constructor
 */
const TooltipLink = props =>
  <TooltipElement
    id={props.id}
    position={props.position}
    tip={props.title}
  >
    <Link {...props}>
      {props.children}
    </Link>
  </TooltipElement>

TooltipLink.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  children: T.node.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left']),
  disabled: T.bool,
  target: T.string,
  className: T.string
}

TooltipLink.defaultProps = {
  position: 'top',
  disabled: false
}

/**
 * Button with tooltip.
 *
 * @param props
 * @constructor
 */
const TooltipButton = props =>
  <TooltipElement
    id={props.id}
    position={props.position}
    tip={props.title}
  >
    <Button {...props}>
      {props.children}
    </Button>
  </TooltipElement>

TooltipButton.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  children: T.node.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left']),
  disabled: T.bool,
  onClick: T.func,
  className: T.string
}

TooltipButton.defaultProps = {
  position: 'top',
  disabled: false
}

export {
  Link,
  Button,
  TooltipLink,
  TooltipButton
}
