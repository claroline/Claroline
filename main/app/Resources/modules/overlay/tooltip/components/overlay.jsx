import React from 'react'
import {PropTypes as T} from 'prop-types'

import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'

import {Tooltip} from '#/main/app/overlay/tooltip/components/tooltip'

const TooltipOverlay = props => !props.disabled ?
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

TooltipOverlay.propTypes = {
  id: T.string.isRequired,
  tip: T.string.isRequired,
  disabled: T.bool,
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
