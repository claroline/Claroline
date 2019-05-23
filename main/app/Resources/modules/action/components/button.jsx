import React from 'react'
import classes from 'classnames'
import invariant from 'invariant'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {registry as buttonRegistry} from '#/main/app/buttons/registry'
import {TooltipOverlay} from '#/main/app/overlay/tooltip/components/overlay'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {createActionDefinition} from '#/main/app/action/utils'

const ButtonComponent = props => {
  const button = buttonRegistry.get(props.type)

  invariant(undefined !== button, `You have requested a non existent button "${props.type}".`)

  return React.createElement(button, Object.assign(
    omit(props, 'type', 'icon', 'label', 'hideLabel', 'subscript')
  ), [
    props.icon &&
      <span key="button-icon" className={classes('action-icon', props.icon, !props.hideLabel && 'icon-with-text-right')} aria-hidden={true} />,
    props.hideLabel ? <span key="button-label" className="sr-only">{props.label}</span> : props.label,
    props.children,
    props.subscript &&
      <span key="button-subscript" className={classes('action-subscript', `${props.subscript.type} ${props.subscript.type}-${props.subscript.status || 'primary'}`)}>{props.subscript.value}</span>
  ])
}

implementPropTypes(ButtonComponent, ActionTypes, {
  hideLabel: T.bool
})

/**
 * Renders the correct button component for an action.
 *
 * @param props
 * @constructor
 */
const Button = props => {
  const actionDef = createActionDefinition(props)

  if (props.tooltip) {
    return (
      <TooltipOverlay
        id={`${actionDef.id}-tip`}
        position={actionDef.tooltip}
        tip={actionDef.label}
        disabled={actionDef.disabled}
      >
        <ButtonComponent
          {...omit(actionDef, 'tooltip', 'group', 'context', 'scope', 'default')}
          hideLabel={true}
        >
          {props.children}
        </ButtonComponent>
      </TooltipOverlay>
    )
  }

  return (
    <ButtonComponent
      {...omit(actionDef, 'tooltip', 'group', 'context', 'scope', 'default')}
    >
      {props.children}
    </ButtonComponent>
  )
}

implementPropTypes(Button, ActionTypes, {
  /**
   * If provided, only the icon of the action will be displayed
   * and the label will be rendered inside a tooltip
   *
   * @type {string}
   */
  tooltip: T.oneOf(['left', 'top', 'right', 'bottom']),

  /**
   * The rendering size of the action.
   *
   * @type {string}
   */
  size: T.oneOf(['sm', 'lg']),
  children: T.any
})

export {
  Button
}
