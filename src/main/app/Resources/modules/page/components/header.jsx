import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'

import {asset} from '#/main/app/config/asset'
import {Button} from '#/main/app/action'
import {Toolbar} from '#/main/app/action/components/toolbar'

import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

import {PageTitle} from '#/main/app/page/components/title'
import {PageNav} from '#/main/app/page/components/nav'
import {Await} from '#/main/app/components/await'

const PageActions = (props) => {
  if (isEmpty(props.actions)) {
    return null
  }

  let actions = [].concat(props.actions)

  let primaryAction
  if (props.primaryAction) {
    const primaryPos = actions.findIndex(action => action.name === props.primaryAction)
    if (-1 !== primaryPos) {
      primaryAction = actions[primaryPos]
      actions.splice(primaryPos, 1)
    }
  }

  let secondaryAction
  if (props.secondaryAction) {
    const secondaryPos = actions.findIndex(action => action.name === props.secondaryAction)
    if (-1 !== secondaryPos) {
      secondaryAction = actions[secondaryPos]
      actions.splice(secondaryPos, 1)
    }
  }

  if (!secondaryAction && 1 === actions.length) {
    secondaryAction = actions[0]
    actions.splice(0, 1)
  }

  return (
    <div className="page-actions gap-3 ms-auto" role="presentation">
      {primaryAction &&
        <Button
          {...primaryAction}
          className="btn btn-primary page-action-btn"
          icon={undefined}
          tooltip={undefined}
          disabled={props.disabled}
        />
      }

      {secondaryAction &&
        <Button
          {...secondaryAction}
          className="btn btn-body page-actions-btn"
          icon={undefined}
          tooltip={undefined}
          disabled={props.disabled}
        />
      }

      {!isEmpty(actions) &&
        <Toolbar
          id="page-actions-toolbar"
          className="btn-toolbar"
          buttonName="btn btn-body page-actions-btn"
          tooltip="bottom"
          toolbar="more"
          actions={actions}
          disabled={props.disabled}
          scope="object"
        />
      }
    </div>
  )
}

PageActions.propTypes = {
  primaryAction: T.string,
  secondaryAction: T.string,
  actions: T.arrayOf(T.shape(
    ActionTypes.propTypes
  )),
  disabled: T.bool
}

/**
 * Header of the current page.
 *
 * Contains title, icon, actions and an optional poster image.
 */
const PageHeader = props =>
  <header
    style={props.poster && {
      backgroundImage: `url("${asset(props.poster)}")`
    }}
    className={classes('page-header', {
      'page-poster': !!props.poster
    })}
  >
    <PageNav embedded={props.embedded}>
      {props.menu}
    </PageNav>

    <div className="page-header-content m-4 gap-4" role="presentation">
      {props.icon &&
        <div className="page-icon" role="presentation">
          {props.icon}
        </div>
      }

      <div className="d-flex flex-fill align-items-center" role="presentation">
        <PageTitle
          embedded={props.embedded}
          path={props.path}
          title={props.title}
        />

        {props.actions instanceof Promise ?
          <Await for={props.actions} then={(resolvedActions) => (
            <PageActions
              actions={resolvedActions}
              primaryAction={props.primaryAction}
              secondaryAction={props.secondaryAction}
              disabled={props.disabled}
            />
          )} /> :
          <PageActions
            actions={props.actions}
            primaryAction={props.primaryAction}
            secondaryAction={props.secondaryAction}
            disabled={props.disabled}
          />
        }
      </div>
    </div>
  </header>

PageHeader.propTypes = {
  id: T.string,
  title: T.node.isRequired,
  icon: T.oneOfType([T.string, T.node]),
  embedded: T.bool,
  poster: T.string,
  disabled: T.bool,

  /**
   * The path of the page inside the application (used to build the breadcrumb).
   */
  path: T.arrayOf(T.shape({
    label: T.string.isRequired,
    displayed: T.bool,
    target: T.oneOfType([T.string, T.array])
  })),

  primaryAction: T.string,
  secondaryAction: T.string,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),

  /**
   * Custom children.
   *
   * Add your custom component here.
   */
  children: T.node
}

PageHeader.defaultProps = {
  actions: []
}

export {
  PageHeader
}
