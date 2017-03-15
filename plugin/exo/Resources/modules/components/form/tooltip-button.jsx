import React, {PropTypes as T} from 'react'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import classes from 'classnames'

// TODO inherit from TooltipElement

export const TooltipButton = props =>
  <OverlayTrigger
    placement={props.position}
    overlay={
      <Tooltip id={props.id}>{props.title}</Tooltip>
    }
  >
    <button
      type="button"
      disabled={!props.enabled}
      className={classes('tooltiped-button', 'btn', props.className, {disabled: !props.enabled})}
      onClick={props.onClick}
    >
      {props.label}
    </button>
  </OverlayTrigger>

TooltipButton.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  position: T.oneOf(['top', 'right', 'bottom', 'left']),
  enabled: T.bool,
  onClick: T.func,
  label: T.node,
  className: T.string
}

TooltipButton.defaultProps = {
  position: 'top',
  enabled: true
}
