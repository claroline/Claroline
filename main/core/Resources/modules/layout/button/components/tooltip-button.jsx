import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
// TODO : find why I can't use the custom Button component (tooltip is not triggered if used)
/*import {Button} from '#/main/core/layout/button/components/button.jsx'*/

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
    <button
      type="button"
      role="button"
      disabled={props.disabled}
      className={classes(
        'btn',
        props.className,
        {disabled: props.disabled}
      )}
      onClick={(e) => !props.disabled && props.onClick(e)}
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
