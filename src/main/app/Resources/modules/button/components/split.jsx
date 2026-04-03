import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {Button} from '#/main/app/action'

import {Action as ActionTypes} from '#/main/app/action/prop-types'
import {MENU_BUTTON} from '#/main/app/buttons'
import {trans} from '#/main/app/intl'

const ButtonSplit = ({
  className,
  buttonName,
  size,
  primaryAction = null,
  actions = [],
  disabled = false
}) => {
  let displayedActions = []
    .concat(actions || [])
    .filter(action => undefined === action.displayed || action.displayed)

  let primary
  if (!isEmpty(displayedActions) && primaryAction) {
    const primaryPos = displayedActions.findIndex(action => action.name === primaryAction)
    if (-1 !== primaryPos) {
      primary = displayedActions[primaryPos]
      displayedActions.splice(primaryPos, 1)
    }
  }

  if (!isEmpty(displayedActions) && !primaryAction) {
    primary = displayedActions[0]
    displayedActions.splice(0, 1)
  }

  if (isEmpty(primary) && isEmpty(displayedActions)) {
    return null
  }

  return (
    <div className={classes('btn-group d-flex flex-nowrap', className)} role="toolbar">
      {primary &&
        <Button
          {...primary}
          className="btn btn-body"
          size={size}
          disabled={disabled || primary.disabled}
        />
      }

      {!isEmpty(displayedActions) &&
        <Button
          className={buttonName}
          type={MENU_BUTTON}
          icon="fa fa-chevron-down text-body-tertiary fs-sm"
          tooltip="bottom"
          label={trans('show-more-actions', {}, 'actions')}
          disabled={disabled}
          menu={{
            items: displayedActions,
            align: 'end'
          }}
          size={size}
        />
      }
    </div>
  )
}

ButtonSplit.propTypes = {
  size: T.oneOf(['sm', 'lg']),
  disabled: T.bool,
  className: T.string,
  buttonName: T.string,

  /**
   * The name of an optional primary action.
   * NB. The action MUST be defined in the `actions` list.
   */
  primaryAction: T.string,

  /**
   * The list of the available actions.
   * NB. This list MUST contain the actions for `primaryAction` if defined.
   *
   * @type {Array}
   */
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
}

export {
  ButtonSplit
}
