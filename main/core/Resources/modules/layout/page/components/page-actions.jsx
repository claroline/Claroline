import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import DropdownButton from 'react-bootstrap/lib/DropdownButton'
import MenuItem from 'react-bootstrap/lib/MenuItem'

import {t} from '#/main/core/translation'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'
import {MenuItemAction} from '#/main/core/layout/components/dropdown.jsx'
import {TooltipAction} from '#/main/core/layout/button/components/tooltip-action.jsx'

/**
 * Base component for each page actions.
 *
 * @param props
 * @constructor
 */
const PageAction = props =>
  <TooltipAction
    id={props.id}
    className={classes('page-action-btn', props.className, {
      'page-action-default': !props.primary && !props.dangerous,
      'page-action-primary': props.primary,
      'page-action-danger': props.dangerous
    })}
    position="bottom"
    icon={props.icon}
    label={props.title}
    action={props.action}
    disabled={props.disabled}
  >
    {props.children}
  </TooltipAction>

PageAction.propTypes = {
  id: T.string.isRequired,
  primary: T.bool,
  dangerous: T.bool,
  title: T.string.isRequired,
  icon: T.string.isRequired,
  disabled: T.bool,
  children: T.node,

  /**
   * Additional CSS classes.
   */
  className: T.string,

  /**
   * The target action of the button.
   */
  action: T.oneOfType([T.string, T.func]).isRequired
}

PageAction.defaultProps = {
  disabled: false,
  primary: false,
  dangerous: false
}

/**
 * Toggles fullscreen mode.
 *
 * @param props
 * @constructor
 */
const FullScreenAction = props =>
  <PageAction
    id="page-fullscreen"
    title={t(props.fullscreen ? 'fullscreen_off' : 'fullscreen_on')}
    icon={classes('fa', {
      'fa-expand': !props.fullscreen,
      'fa-compress': props.fullscreen
    })}
    action={props.toggleFullscreen}
  />

FullScreenAction.propTypes = {
  fullscreen: T.bool.isRequired,
  toggleFullscreen: T.func.isRequired
}

const MoreAction = props => {
  // set defaults
  const actions = props.actions.map(action => Object.assign({}, {
    displayed: true,
    disabled: false,
    dangerous: false
  }, action))

  // filters and groups actions
  const unclassifiedActions = actions.filter(action => action.displayed && !action.dangerous && !action.group)
  const dangerousActions = actions.filter(action => action.displayed && action.dangerous)

  // generate actions groups
  const groupActions = {}
  for (let i=0; i < actions.length; i++) {
    const action = actions[i]
    if (action.displayed && !action.dangerous && !!action.group) {
      if (!groupActions[action.group]) {
        groupActions[action.group] = []
      }

      groupActions[action.group].push(action)
    }
  }

  return (
    <TooltipElement
      id="page-more-title"
      position="bottom"
      tip={t('show_more_actions')}
    >
      <DropdownButton
        id="page-more"
        title={<span className="action-icon fa fa-ellipsis-v" />}
        className="btn page-action-btn page-action-default"
        noCaret={true}
        pullRight={true}
      >
        {0 !== unclassifiedActions.length &&
          <MenuItem header={true}>{props.title}</MenuItem>
        }

        {unclassifiedActions.map((action, actionIndex) =>
          <MenuItemAction
            key={`page-more-${actionIndex}`}
            {...action}
          />
        )}

        {Object.keys(groupActions).map((group, groupIndex) => [
          <MenuItem key={`page-more-group-${groupIndex}`} header={true}>{group}</MenuItem>,
          ...groupActions[group].map((action, actionIndex) =>
            <MenuItemAction
              key={`page-more-group-action${actionIndex}`}
              {...action}
            />
          )
        ])}

        {0 !== dangerousActions.length &&
          <MenuItem divider />
        }

        {dangerousActions.map((action, actionIndex) =>
          <MenuItemAction
            key={`page-more-dangerous-${actionIndex}`}
            {...action}
          />
        )}

      </DropdownButton>
    </TooltipElement>
  )
}

MoreAction.propTypes = {
  title: T.string,
  actions: T.arrayOf(T.shape({
    icon: T.string,
    label: T.string.isRequired,
    action: T.oneOfType([T.string, T.func]).isRequired,
    group: T.string,
    dangerous: T.bool,
    displayed: T.bool,
    disabled: T.bool
  })).isRequired
}

MoreAction.defaultProps = {
  title: t('more_actions')
}

/**
 * Groups some actions together.
 *
 * @param props
 * @constructor
 */
const PageGroupActions = props =>
  <div className={classes('page-actions-group', props.className)}>
    {props.children}
  </div>

PageGroupActions.propTypes = {
  className: T.string,
  children: T.node.isRequired
}

/**
 * Creates actions bar for a page.
 *
 * @param props
 * @constructor
 */
const PageActions = props =>
  <nav className={classes('page-actions', props.className)}>
    {props.children}
  </nav>

PageActions.propTypes = {
  className: T.string,
  children: T.node.isRequired
}

export {
  PageAction,
  FullScreenAction,
  MoreAction,
  PageGroupActions,
  PageActions
}
