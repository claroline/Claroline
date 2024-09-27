import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'

import {Await} from '#/main/app/components/await'
import {PageActions} from '#/main/app/page/components/actions'
import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

const PageTitle = props =>
  <header className={classes('py-4 pt-5 d-flex gap-3 align-items-end', props.className, props.size && `content-${props.size}`)}>
    <h1 className="page-title m-0">
      {props.title}
    </h1>

    {props.actions instanceof Promise ?
      <Await for={props.actions} then={(resolvedActions) => (
        <PageActions
          actions={resolvedActions}
          primaryAction={props.primaryAction}
          secondaryAction={props.secondaryAction}
          toolbar={props.toolbar}
          disabled={props.disabled}
        />
      )} /> :
      <PageActions
        actions={props.actions}
        primaryAction={props.primaryAction}
        secondaryAction={props.secondaryAction}
        toolbar={props.toolbar}
        disabled={props.disabled}
      />
    }
  </header>

PageTitle.propTypes = {
  className: T.string,
  size: T.oneOf(['sm', 'md', 'lg', 'full']),
  title: T.string.isRequired,
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

PageTitle.defaultProps = {
  actions: [],
  disabled: false,
  toolbar: 'more'
}

export {
  PageTitle
}
