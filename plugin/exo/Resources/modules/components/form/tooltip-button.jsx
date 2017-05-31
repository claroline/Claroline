import React, {Component} from 'react'
import {PropTypes as T} from 'prop-types'
import OverlayTrigger from 'react-bootstrap/lib/OverlayTrigger'
import Tooltip from 'react-bootstrap/lib/Tooltip'
import classes from 'classnames'

// TODO replace with the one from the core (not done for now because structure has slightly changed)
// TODO inherit from TooltipElement

export class TooltipButton extends Component {
  render() {
    return (
      <OverlayTrigger
        placement={this.props.position}
        overlay={
          <Tooltip id={this.props.id}>{this.props.title}</Tooltip>
        }
      >
        <button
          type="button"
          disabled={!this.props.enabled}
          className={classes('tooltiped-button', 'btn', this.props.className, {disabled: !this.props.enabled})}
          onClick={this.props.onClick}
        >
          {this.props.label}
        </button>
      </OverlayTrigger>
    )
  }
}

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
