import React from 'react'
import {PropTypes as T} from 'prop-types'

import {trans, transChoice} from '#/main/core/translation'
import {toKey} from '#/main/core/scaffolding/text/utils'

import {GenericButton} from '#/main/app/button/components/generic'
import {Button} from '#/main/app/action/components/button'
import {Action as ActionTypes} from '#/main/app/action/prop-types'

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
  <Button
    id={`${props.id}-btn`}
    className="data-actions-btn btn btn-link"
    type="menu"
    tooltip="left"
    icon="fa fa-fw fa-ellipsis-v"
    label={trans('show-actions', {}, 'actions')}
    menu={{
      label: trans('actions'),
      align: 'right',
      items: props.actions
    }}
  />

ListActions.propTypes = {
  id: T.string.isRequired,
  actions: T.arrayOf(
    T.shape(ActionTypes.propTypes)
  ).isRequired
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
      {props.actions
        .filter(action => undefined === action.displayed || action.displayed)
        .map((action) =>
          <Button
            {...action}
            key={toKey(action.label)}
            className="btn btn-link"
            tooltip="top"
          />
        )
      }
    </div>
  </div>

ListBulkActions.propTypes = {
  count: T.number.isRequired,
  actions: T.arrayOf(
    T.shape(ActionTypes.propTypes)
  ).isRequired
}

export {
  ListActions,
  ListBulkActions,
  ListPrimaryAction
}
