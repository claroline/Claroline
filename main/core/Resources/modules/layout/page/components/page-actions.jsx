import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import DropdownButton from 'react-bootstrap/lib/DropdownButton'

import {t} from '#/main/core/translation'
import {TooltipElement} from '#/main/core/layout/components/tooltip-element.jsx'

/**
 * Base component for each page actions.
 *
 * @param props
 * @constructor
 */
const PageAction = props =>
  <TooltipElement
    id={props.id}
    position="bottom"
    tip={props.title}
  >
    {typeof props.action === 'function' ?
      <button
        type="button"
        role="button"
        className={classes(
          'btn page-action-btn',
          {
            'disabled': props.disabled,
            'page-action-primary': props.primary
          },
          props.className
        )}
        disabled={props.disabled}
        onClick={() => !props.disabled && props.action()}
      >
        <span className={classes('page-action-icon', props.icon)} aria-hidden={true} />
        {props.children}
      </button>
      :
      <a
        role="link"
        className={classes(
          'btn page-action-btn',
          {
            'disabled': props.disabled,
            'page-action-primary': props.primary
          },
          props.className
        )}
        disabled={props.disabled}
        href={!props.disabled ? props.action : ''}
      >
        <span className={classes('page-action-icon', props.icon)} aria-hidden={true} />
        {props.children}
      </a>
    }
  </TooltipElement>

PageAction.propTypes = {
  id: T.string.isRequired,
  primary: T.bool,
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
  primary: false
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

const MoreAction = props =>
  <TooltipElement
    id="page-more-title"
    position="bottom"
    tip={t('show_more_actions')}
  >
    <DropdownButton
      id="page-more"
      title={<span className="page-action-icon fa fa-ellipsis-v" />}
      bsStyle=""
      className="btn page-action-btn"
      noCaret={true}
      pullRight={true}
    >
      {props.children}
    </DropdownButton>
  </TooltipElement>

MoreAction.propTypes = {
  children: T.node.isRequired
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
