import React, {createElement} from 'react'
import {PropTypes as T} from 'prop-types'
import classes from 'classnames'
import merge from 'lodash/merge'

import {Action as ActionTypes, PromisedAction as PromisedActionTypes} from '#/main/app/action/prop-types'

const GridItem = props =>
  <li className="data-grid-item-container">
    {createElement(props.card, {
      className: classes({
        'data-card-selected': props.selected
      }),
      size: props.size,
      orientation: props.orientation,
      data: props.row,
      primaryAction: props.primaryAction,
      actions: props.actions
    })}

    {props.onSelect &&
      <input
        type="checkbox"
        className="data-grid-item-select form-check-input"
        checked={props.selected}
        onChange={props.onSelect}
      />
    }
  </li>

GridItem.propTypes = {
  size: T.string.isRequired,
  orientation: T.string.isRequired,
  row: T.object.isRequired,

  primaryAction:  T.oneOfType([
    // a regular action
    T.shape(merge({}, ActionTypes.propTypes, {
      label: T.node // make label optional
    })),
    // a promise that will resolve a list of actions
    T.shape(
      PromisedActionTypes.propTypes
    )
  ]),

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

  card: T.func.isRequired, // It must be a React component.
  selected: T.bool,
  onSelect: T.func
}

GridItem.defaultProps = {
  selected: false
}

export {
  GridItem
}
