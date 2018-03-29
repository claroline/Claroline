import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {TooltipElement} from '#/main/core/layout/components/tooltip-element'
import {DropdownButton, MenuItem} from '#/main/core/layout/components/dropdown'

import {trans} from '#/main/core/translation'
import {Action as ActionTypes} from '#/main/core/layout/action/prop-types'

const ActionMenuItem = props => {
  // construct action prop
  const action = {}
  if (typeof props.action === 'function') {
    action.onClick = (e) => {
      if (!props.disabled) {
        props.action(e)
      }

      e.preventDefault()
      e.stopPropagation()

      e.target.blur()
    }
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
        <span className={props.icon} aria-hidden="true" role="presentation" />
      }

      {props.label}
    </MenuItem>
  )
}

ActionMenuItem.propTypes = {
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

ActionMenuItem.defaultProps = {
  disabled: false,
  dangerous: false
}

/**
 * Renders a dropdown of actions.
 *
 * @param props
 * @constructor
 */
const ActionDropdownButton = props => {
  const displayedActions = props.actions.filter(
    action => undefined === action.displayed || action.displayed
  )

  const actions          = displayedActions.filter(action => !action.dangerous)
  const dangerousActions = displayedActions.filter(action =>  action.dangerous)

  // only display button if there are actions
  return (0 !== displayedActions.length) && (
    <TooltipElement
      id={`${props.id}-tip`}
      tip={props.title}
      position="left"
    >
      <DropdownButton
        id={`${props.id}-btn`}
        title={<span className="fa fa-fw fa-ellipsis-v" />}
        className={props.className}
        bsStyle={props.bsStyle}
        noCaret={props.noCaret}
        pullRight={props.pullRight}
      >
        <MenuItem header>{trans(props.title)}</MenuItem>

        {actions.map((action, actionIndex) =>
          <ActionMenuItem
            key={`${props.id}-action-${actionIndex}`}
            {...action}
          />
        )}

        {(0 !== actions.length && 0 !== dangerousActions.length) &&
          <MenuItem divider />
        }

        {dangerousActions.map((action, actionIndex) =>
          <ActionMenuItem
            key={`${props.id}-action-dangerous-${actionIndex}`}
            {...action}
          />
        )}
      </DropdownButton>
    </TooltipElement>
  )
}

ActionDropdownButton.propTypes = {
  id: T.string.isRequired,
  title: T.string,
  bsStyle: T.string,
  noCaret: T.bool,
  pullRight: T.bool,
  className: T.string,
  actions: T.arrayOf(
    T.shape(ActionTypes.propTypes)
  ).isRequired
}

ActionDropdownButton.defaultProps = {
  title: trans('actions')
}

export {
  ActionDropdownButton,
  ActionMenuItem
}
