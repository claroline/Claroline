import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Await} from '#/main/app/components/await'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'
import {PageActions} from '#/main/app/page/components/actions'

const PageHeading = props =>
  <header className={classes('app-page-heading px-4', props.className, props.size && `content-${props.size}`)}>
    {props.icon &&
      <div className="app-page-icon d-inline-block" role="presentation" aria-hidden={true}>
        {props.icon}
      </div>
    }

    <div className={classes('app-page-heading pb-4 d-flex gap-3 align-items-end flex-wrap flex-md-nowrap', {
      'pt-5': !props.icon,
      'pt-2': !!props.icon
    })} role="presentation">
      <h1 className="app-page-title m-0">
        {props.title}
        {props.subtitle &&
          <small className="text-body-secondary">{props.subtitle}</small>
        }
      </h1>

      {props.actions instanceof Promise ?
        <Await for={props.actions} then={(resolvedActions) => (
          <PageActions
            actions={resolvedActions}
            toolbar={props.toolbar}
            primaryAction={props.primaryAction}
            secondaryAction={props.secondaryAction}
            disabled={props.disabled}
          />
        )} /> :
        <PageActions
          actions={props.actions}
          toolbar={props.toolbar}
          primaryAction={props.primaryAction}
          secondaryAction={props.secondaryAction}
          disabled={props.disabled}
        />
      }
    </div>
  </header>

PageHeading.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'md', 'lg', 'full']),
  /**
   * An optional icon for the page.
   * NB. we also use it to display a progression gauge.
   *
   * @type {string}
   */
  icon: T.element,
  title: T.oneOfType([T.string, T.element]).isRequired,
  subtitle: T.string,
  primaryAction: T.string,
  secondaryAction: T.string,
  toolbar: T.string,
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
  disabled: T.bool
}

PageHeading.defaultProps = {
  actions: [],
  disabled: false,
  toolbar: 'more'
}

export {
  PageHeading
}
