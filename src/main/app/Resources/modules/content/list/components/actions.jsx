import React from 'react'
import {PropTypes as T} from 'prop-types'
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

// TODO : maybe manage it in action module (it's duplicated for cards)
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
      className={props.className}
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
 * Actions available for a single data item.
 */
const ListActions = props =>
  <Toolbar
    id={`${props.id}-btn`}
    className="data-actions"
    buttonName="btn btn-link"
    tooltip="left"
    toolbar="more"
    actions={props.actions}
    scope="object"
  />

ListActions.propTypes = {
  id: T.string.isRequired,
  actions: T.oneOfType([
    // a regular array of actions
    T.arrayOf(T.shape(
      ActionTypes.propTypes
    )),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]).isRequired
}

/**
 * Bulk actions available for selected data items.
 */
const ListBulkActions = props =>
  <div className="data-bulk-actions list-selected">
    <div className="list-selected-label">
      <span className="fa fa-level-up fa-rotate-90" />
      {transChoice('list_selected_count', props.count, {count: props.count}, 'platform')}
    </div>

    {props.actions &&
      <div className="list-selected-actions">
        <Toolbar
          buttonName="btn btn-link"
          tooltip="left"
          actions={props.actions}
          scope="collection"
        />
      </div>
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
  ListActions,
  ListBulkActions,
  ListPrimaryAction
}
