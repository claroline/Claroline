import React from 'react'
import {PropTypes as T} from 'prop-types'

import {OverlayTrigger} from '#/main/app/overlays/components/overlay'
import {Tooltip} from '#/main/app/overlays/tooltip/components/tooltip'

const TooltipOverlay = props => {
  if (props.disabled) {
    return props.children
  }

  return (
    <OverlayTrigger
      placement={props.position}
      overlay={
        <Tooltip id={props.id}>{props.tip}</Tooltip>
      }
    >
      {props.children}
    </OverlayTrigger>
  )
}

TooltipOverlay.propTypes = {
  id: T.string.isRequired,
  tip: T.string.isRequired,
  disabled: T.bool,
  /**
   * ATTENTION : children need to be able to receive a React ref (either by being a class component or by using `forwardRef`).
   */
  children: T.element.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left'])
}

TooltipOverlay.defaultProps = {
  position: 'top',
  disabled: false
}

export {
  TooltipOverlay
}
