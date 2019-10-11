import React from 'react'
import classes from 'classnames'
import omit from 'lodash/omit'

import {PropTypes as T, implementPropTypes} from '#/main/app/prop-types'

import {Await} from '#/main/app/components/await'
import {Button} from '#/main/app/action/components/button'
import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes,
  Toolbar as ToolbarTypes
} from '#/main/app/action/prop-types'

import {buildToolbar} from '#/main/app/action/utils'

/**
 * Creates a toolbar of actions.
 *
 * @param props
 * @constructor
 */
const StaticToolbar = props => {
  const toolbar = buildToolbar(props.toolbar, props.actions, props.scope)

  return (0 !== toolbar.length &&
    <nav role="toolbar" className={props.className}>
      {toolbar.map((group, groupIndex) => [
        0 !== groupIndex &&
          <span
            key={`separator-${groupIndex}`}
            className={`${props.className}-separator`}
          />,
        ...group.map((action) =>
          <Button
            {...omit(action, 'name')}
            id={`${props.id}${action.id || action.name}`}
            key={action.id || action.name}
            disabled={props.disabled || action.disabled}
            className={classes(`${props.className}-btn`, props.buttonName, action.className)}
            tooltip={props.tooltip}
            size={props.size}
          />
        )
      ])}
    </nav>
  ) || null
}

implementPropTypes(StaticToolbar, ToolbarTypes, {
  // a regular array of actions
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  ))
}, {
  actions: []
})

const PromisedToolbar = props =>
  <Await
    for={props.actions}
    placeholder={
      <div className={props.className}>
        <span className={classes(`${props.className}-btn`, props.buttonName, 'default')}>
          <span className="fa fa-fw fa-spinner fa-spin" />
        </span>
      </div>
    }
    then={(resolvedActions) => (
      <StaticToolbar {...props} actions={resolvedActions} />
    )}
  />

implementPropTypes(PromisedToolbar, ToolbarTypes, {
  // a promise that will resolve a list of actions
  actions: T.shape(
    PromisedActionTypes.propTypes
  )
})

const Toolbar = props => props.actions instanceof Promise ?
  <PromisedToolbar {...props} /> :
  <StaticToolbar {...props} />

implementPropTypes(Toolbar, ToolbarTypes)

export {
  Toolbar
}
