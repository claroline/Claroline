import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

// @deprecated use Button from `#/main/app/action`

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
    disabled={props.disabled}
  >
    <button
      type="button"
      role="button"
      disabled={props.disabled}
      className={classes(
        'btn',
        props.className,
        {disabled: props.disabled}
      )}
      onClick={(e) => {
        if (!props.disabled) {
          props.onClick(e)
        }

        e.preventDefault()
        e.stopPropagation()

        e.target.blur()
      }}
    >
      {props.children}
    </button>
  </TooltipElement>

TooltipButton.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  children: T.node.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left']),
  disabled: T.bool,
  onClick: T.func.isRequired,
  className: T.string
}

TooltipButton.defaultProps = {
  position: 'top',
  disabled: false
}

export {
  TooltipButton
}
