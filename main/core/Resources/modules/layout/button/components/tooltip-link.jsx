import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

// @deprecated use Button from `#/main/app/action`

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
    disabled={props.disabled}
  >
    <a
      href={!props.disabled ? props.target : ''}
      disabled={props.disabled}
      className={classes('btn', props.className, {
        disabled: props.disabled
      })}
    >
      {props.children}
    </a>
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

export {
  TooltipLink
}
