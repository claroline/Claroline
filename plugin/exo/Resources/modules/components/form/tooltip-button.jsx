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

TooltipButton.defaultProps = {
  position: 'top',
  enabled: true
}

TooltipButton.propTypes = {
  id: T.string.isRequired,
  title: T.string.isRequired,
  position: T.string.isRequired,
  enabled: T.bool.isRequired,
  onClick: T.func,
  label: T.string,
  className: T.string
}
