import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import {MenuItem} from 'react-bootstrap'

const MenuItemAction = props => {
  // construct action prop
  const action = {}
  if (typeof props.action === 'function') {
    action.onClick = (e) => !props.disabled && props.action(e)
  } else {
    action.href = !props.disabled ? props.action : ''
  }

  return (
    <MenuItem
      eventKey={props.eventKey}
      className={classes({
        'dropdown-link-danger': props.dangerous
      })}
      disabled={props.disabled}
      onSelect={props.onSelect}
      {...action}
    >
      {props.icon &&
        <span className={props.icon} aria-hidden="true" role="presentation"/>
      }
      {props.label}
    </MenuItem>
  )
}

MenuItemAction.propTypes = {
  /**
   * An optional icon associated to the action
   */
  icon: T.string,

  /**
   * The translated name of the action.
   */
  label: T.string.isRequired,

  /**
   * The action to execute.
   * Either a URL to follow or a function to call.
   */
  action: T.oneOfType([T.string, T.func]),

  disabled: T.bool,
  dangerous: T.bool,

  // From MenuItem
  eventKey: T.any,
  onSelect: T.func
}

MenuItemAction.defaultProps = {
  disabled: false,
  dangerous: false
}

export {
  MenuItemAction
}