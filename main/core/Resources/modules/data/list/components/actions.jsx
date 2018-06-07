import React from 'react'
import {PropTypes as T} from 'prop-types'

import {transChoice} from '#/main/core/translation'

import {GenericButton} from '#/main/app/button/components/generic'
import {Toolbar} from '#/main/app/action/components/toolbar'
import {
  Action as ActionTypes,
  PromisedAction as PromisedActionTypes
} from '#/main/app/action/prop-types'

const ListPrimaryAction = props => {
  if (!props.action || props.action.disabled) {
    return React.createElement(props.disabledWrapper, {
      className: props.className
    }, props.children)
  } else {
    return (
      <GenericButton
        {...props.action}
        className={props.className}
      >
        {props.children}
      </GenericButton>
    )
  }
}

ListPrimaryAction.propTypes = {
  className: T.string,
  action: T.shape(
    ActionTypes.propTypes
  ),
  disabledWrapper: T.string,
  children: T.any.isRequired
}

ListPrimaryAction.defaultProps = {
  disabled: false,
  disabledWrapper: 'span'
}

/**
 * Actions available for a single data item.
 *
 * @param props
 * @constructor
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
 *
 * @param props
 * @constructor
 *
 * @todo create and use an action bar
 */
const ListBulkActions = props =>
  <div className="data-bulk-actions list-selected">
    <div className="list-selected-label">
      <span className="fa fa-level-up fa-rotate-90" />
      {transChoice('list_selected_count', props.count, {count: props.count}, 'platform')}
    </div>

    <div className="list-selected-actions">
      <Toolbar
        buttonName="btn btn-link"
        tooltip="left"
        actions={props.actions}
        scope="collection"
      />
    </div>
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
  ]).isRequired
}

export {
  ListActions,
  ListBulkActions,
  ListPrimaryAction
}
