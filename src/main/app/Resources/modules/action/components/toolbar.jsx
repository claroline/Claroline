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
 */
const StaticToolbar = (props) => {
  const toolbar = buildToolbar(props.toolbar, props.actions, props.scope)

  return (0 !== toolbar.length &&
    <nav role={props.role} className={classes(props.className, props.name)} style={props.style}>
      {toolbar.map((group, groupIndex) => [
        0 !== groupIndex && props.separatorName &&
          <span
            key={`separator-${groupIndex}`}
            className={`${props.name}-separator ${props.separatorName}`}
          />,
        ...group.map((action) =>
          <Button
            {...omit(action, 'name', 'className')}
            id={`${props.id || props.name || ''}${action.id || action.name}`}
            key={action.id || action.name}
            disabled={props.disabled || action.disabled}
            className={classes(props.buttonName,
              props.name ? `${props.name}-btn` : null,
              action.primary && props.primaryName,
              action.dangerous && props.dangerousName,
              (!action.primary && !action.dangerous) && props.defaultName,
              action.className
            )}
            tooltip={undefined !== action.tooltip ? action.tooltip : props.tooltip}
            size={action.size || props.size}
            variant={props.variant}
            onClick={action.onClick ? () => {
              action.onClick()
              if (props.onClick) {
                props.onClick()
              }
            } : props.onClick}
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

const PromisedToolbar = (props) =>
  <Await
    for={props.actions}
    placeholder={
      <div className={props.className} role="progressbar">
        <span className={classes(props.name ? `${props.name}-btn` : null, props.buttonName, 'default')}>
          <span className="fa fa-fw fa-spinner fa-spin" />
        </span>
      </div>
    }
    then={(resolvedActions) => (
      <StaticToolbar {...props} actions={resolvedActions} onClick={props.onClick} />
    )}
  />

implementPropTypes(PromisedToolbar, ToolbarTypes, {
  // a promise that will resolve a list of actions
  actions: T.shape(
    PromisedActionTypes.propTypes
  )
})

const Toolbar =  (props) => props.actions instanceof Promise ?
  <PromisedToolbar {...props} /> :
  <StaticToolbar {...props} />

implementPropTypes(Toolbar, ToolbarTypes)

export {
  Toolbar
}
