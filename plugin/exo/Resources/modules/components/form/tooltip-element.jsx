import React, {PropTypes as T} from 'react'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'

export const TooltipElement = props =>
  <OverlayTrigger
    placement={props.position}
    overlay={
      <Tooltip id={props.id}>{props.tip}</Tooltip>
    }
  >
    {props.children}
  </OverlayTrigger>

TooltipElement.defaultProps = {
  position: 'top'
}

TooltipElement.propTypes = {
  id: T.string.isRequired,
  tip: T.string.isRequired,
  children: T.node.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left'])
}
