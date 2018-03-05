import React from 'react'
import {PropTypes as T} from 'prop-types'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'

const TooltipElement = props => !props.disabled ?
  <OverlayTrigger
    placement={props.position}
    overlay={
      <Tooltip id={props.id}>{props.tip}</Tooltip>
    }
  >
    {props.children}
  </OverlayTrigger>
  :
  props.children

TooltipElement.propTypes = {
  id: T.string.isRequired,
  tip: T.string.isRequired,
  disabled: T.bool,
  children: T.element.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left'])
}

TooltipElement.defaultProps = {
  position: 'top',
  disabled: false
}

export {
  TooltipElement
}
