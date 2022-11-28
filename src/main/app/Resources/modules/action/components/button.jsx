import React, {Component, cloneElement} from 'react'
import classes from 'classnames'
import invariant from 'invariant'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'
import {registry as buttonRegistry} from '#/main/app/buttons/registry'
import {TooltipOverlay} from '#/main/app/overlays/tooltip/components/overlay'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {createActionDefinition} from '#/main/app/action/utils'

const ButtonComponent = props => {
  const button = buttonRegistry.get(props.type)

  invariant(undefined !== button, `You have requested a non existent button "${props.type}".`)

  return React.createElement(button, omit(props, 'type', 'icon', 'label', 'hideLabel', 'subscript'), [
    (props.icon && typeof props.icon === 'string') &&
      <span key="button-icon" className={classes('action-icon', props.icon, !props.hideLabel && 'icon-with-text-right')} aria-hidden={true} />,
    (props.icon && typeof props.icon !== 'string') && cloneElement(props.icon, {key: 'button-icon'}),
    props.hideLabel ? <span key="button-label" className="action-label sr-only">{props.label}</span> : props.label,
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
class Button extends Component {
  constructor(props) {
    super(props)

    this.state = {
      error: null
    }
  }

  static getDerivedStateFromError(error) {
    // Update state so the next render will show the fallback UI.
    return {
      error: error
    }
  }

  render() {
    if (this.state.error) {
      // TODO better
      return (
        <span className={this.props.className}>error</span>
      )
    }

    const actionDef = createActionDefinition(this.props)

    if (this.props.tooltip) {
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
            {this.props.children}
          </ButtonComponent>
        </TooltipOverlay>
      )
    }

    return (
      <ButtonComponent
        {...omit(actionDef, 'tooltip', 'group', 'context', 'scope', 'default')}
      >
        {this.props.children}
      </ButtonComponent>
    )
  }
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
  size: T.oneOf(['xs', 'sm', 'lg']),
  children: T.any
})

export {
  Button
}
