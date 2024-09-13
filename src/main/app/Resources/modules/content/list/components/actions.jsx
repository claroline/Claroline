import React from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import isEmpty from 'lodash/isEmpty'
import merge from 'lodash/merge'

import {transChoice} from '#/main/app/intl/translation'
import {Await} from '#/main/app/components/await'
import {Button} from '#/main/app/action/components/button'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes
} from '#/main/app/action/prop-types'
import isArray from 'lodash/isArray'

const StaticPrimaryAction = props => {
  if (isEmpty(props.action) || props.action.disabled || (props.action.displayed !== undefined && !props.action.displayed)) {
    return (
      <span className={props.className}>
        {props.children}
      </span>
    )
  }

  return (
    <Button
      {...props.action}
      icon={undefined}
      label={props.children}
      className={classes('btn btn-link p-0', props.className)}
    />
  )
}

StaticPrimaryAction.propTypes = {
  className: T.string,
  action: T.shape(merge({}, ActionTypes.propTypes, {
    label: T.node // make label optional
  })),
  children: T.node.isRequired
}

const ListPrimaryAction = props => {
  if (props.action instanceof Promise) {
    return (
      <Await
        for={props.action}
        then={action => (
          <StaticPrimaryAction
            className={props.className}
            action={action}
          >
            {props.children}
          </StaticPrimaryAction>
        )}
        placeholder={
          <span className={props.className}>
            {props.children}
          </span>
        }
      />
    )
  }

  return (
    <StaticPrimaryAction
      className={props.className}
      action={props.action}
    >
      {props.children}
    </StaticPrimaryAction>
  )
}

ListPrimaryAction.propTypes = {
  className: T.string,
  action: T.oneOfType([
    // a regular action
    T.shape(merge({}, ActionTypes.propTypes, {
      label: T.node // make label optional
    })),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),
  children: T.node.isRequired
}

/**
 * Bulk actions available for selected data items.
 */
const ListBulkActions = props =>
  <div className="data-bulk-actions list-selected text-primary-emphasis bg-primary-subtle">
    <div className="list-selected-label">
      <span className="fa fa-level-up fa-rotate-90 fa-fw icon-with-text-right" />
      {transChoice('list_selected_count', props.count, {count: props.count}, 'platform')}
    </div>

    {props.actions &&
      <Toolbar
        className="list-selected-actions"
        buttonName="btn btn-link"
        actions={isArray(props.actions) ?
          props.actions.map(action => ({...action, icon: null})) :
          props.actions.then(actions => actions.map(action => ({...action, icon: null})))
        }
        scope="collection"
      />
    }
  </div>

ListBulkActions.propTypes = {
  count: T.number.isRequired,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ])
}

export {
  ListBulkActions,
  ListPrimaryAction
}
