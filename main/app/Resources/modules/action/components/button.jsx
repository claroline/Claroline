import React from 'react'
import classes from 'classnames'
import invariant from 'invariant'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {toKey} from '#/main/core/scaffolding/text/utils'
import {registry as buttonRegistry} from '#/main/app/buttons/registry'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element'

import {Action as ActionTypes} from '#/main/app/action/prop-types'

// todo : move tooltip management inside buttons module

const ButtonComponent = props => {
  const button = buttonRegistry.get(props.type)

  invariant(undefined !== button, `You have requested a non existent button "${props.type}".`)

  return React.createElement(button, Object.assign(
    omit(props, 'type', 'icon', 'label', 'subscript', 'hideLabel'),
    {
      id: props.id || (typeof props.label === 'string' ? toKey(props.label) : undefined),
      confirm: props.confirm ? Object.assign({}, props.confirm, {
        // append some defaults from action spec
        icon: props.icon,
        title: props.confirm.title || props.label,
        button: props.confirm.button || props.label
      }) : undefined
    }
  ), [
    props.icon &&
      <span key="button-icon" className={classes('action-icon', props.icon, !props.hideLabel && 'icon-with-text-right')} aria-hidden={true} />,
    props.hideLabel ? <span key="button-label" className="sr-only">{props.label}</span> : props.label,
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
const Button = props => props.tooltip ?
  <TooltipElement
    id={`${props.id || toKey(props.label)}-tip`}
    position={props.tooltip}
    tip={props.label}
    disabled={props.disabled}
  >
    <ButtonComponent
      {...omit(props, 'tooltip', 'group', 'context', 'scope')}
      hideLabel={true}
    />
  </TooltipElement> :
  <ButtonComponent
    {...omit(props, 'tooltip', 'group', 'context', 'scope')}
  />

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
  size: T.oneOf(['sm', 'lg'])
})

export {
  Button
}
