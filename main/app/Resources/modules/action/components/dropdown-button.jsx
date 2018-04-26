import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import omit from 'lodash/omit'

import {
  Dropdown,
  MenuItem as BaseMenuItem
} from '#/main/core/layout/components/dropdown'

import {toKey} from '#/main/core/scaffolding/text/utils'
import {trans} from '#/main/core/translation'
import {MenuItem} from '#/main/app/action/components/menu-item'
import {Button} from '#/main/app/action/components/button'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

const DropdownButton = props => {
  const displayedActions = props.actions.filter(
    action => undefined === action.displayed || action.displayed
  )

  // filters and groups actions
  const unclassifiedActions = displayedActions.filter(action => !action.dangerous && !action.group)
  const dangerousActions    = displayedActions.filter(action => action.dangerous)

  // generate actions groups
  const groupActions = {}
  for (let i=0; i < displayedActions.length; i++) {
    const action = displayedActions[i]
    if (!action.dangerous && !!action.group) {
      if (!groupActions[action.group]) {
        groupActions[action.group] = []
      }

      groupActions[action.group].push(action)
    }
  }

  // only display button if there are actions
  return (0 !== displayedActions.length) && (
    <Dropdown
      id={props.id || toKey(props.label)}
      pullRight={props.pullRight}
    >
      <Button
        {...omit(props, 'actions', 'menuLabel', 'pullRight')}
        id={`${props.id || toKey(props.label)}-toggle`}
        className={classes('dropdown-toggle', props.className)}
        type="callback"
        callback={() => true}
        bsRole="toggle"
      />

      <Dropdown.Menu>
        {(props.menuLabel && 0 !== unclassifiedActions.length) &&
          <BaseMenuItem header={true}>{props.menuLabel}</BaseMenuItem>
        }

        {unclassifiedActions.map((action) =>
          <MenuItem key={toKey(action.label)} {...action} />
        )}

        {Object.keys(groupActions).map((group) => [
          <BaseMenuItem key={toKey(group)} header={true}>{group}</BaseMenuItem>,
          ...groupActions[group].map((action) =>
            <MenuItem key={toKey(action.label)} {...action} />
          )
        ])}

        {((0 !== unclassifiedActions.length || 0 !== Object.keys(groupActions).length) && 0 !== dangerousActions.length) &&
          <BaseMenuItem divider={true} />
        }

        {dangerousActions.map((action) =>
          <MenuItem key={toKey(action.label)} {...action} />
        )}
      </Dropdown.Menu>
    </Dropdown>
  )
}

DropdownButton.propTypes = {
  id: T.string,
  icon: T.string,
  label: T.string,
  pullRight: T.bool,
  className: T.string,
  menuLabel: T.string,

  disabled: T.bool,
  primary: T.bool,
  dangerous: T.bool,

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

  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )).isRequired
}

DropdownButton.defaultProps = {
  icon: 'fa fa-fw fa-ellipsis-v',
  label: trans('actions'),
  menuLabel: trans('actions')
}

export {
  DropdownButton
}
